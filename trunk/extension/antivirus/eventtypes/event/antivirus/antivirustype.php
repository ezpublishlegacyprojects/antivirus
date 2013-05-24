<?php

class AntivirusType extends eZWorkflowEventType
{
	const WORKFLOW_TYPE_STRING = 'antivirus';

    function AntivirusType()
    {
        $this->eZWorkflowEventType( AntivirusType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'kernel/workflow/event', "Antivirus" ) );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'before' ) ) ) );
    }
    
    function execute( $process, $event )
    {
        eZDebugSetting::writeDebug( 'kernel-workflow-antivirus', $process, 'antivirusType::execute' );
        eZDebugSetting::writeDebug( 'kernel-workflow-antivirus', $event, 'antivirusType::execute' );
        $parameters = $process->attribute( 'parameter_list' );
        $versionID = $parameters['version'];
        $object = eZContentObject::fetch( $parameters['object_id'] );
        if ( !$object )
        {
            eZDebugSetting::writeError( 'kernel-workflow-approve', $parameters['object_id'], 'eZXApprove2Type::execute' );
            return eZWorkflowEventType::STATUS_WORKFLOW_CANCELLED;
        }
        if ( $process->attribute( 'user_id' ) == 0 )
        {
            $user = eZUser::currentUser();
            $process->setAttribute( 'user_id', $user->id() );
        }
        else
        {
            $user = eZUser::instance( $process->attribute( 'user_id' ) );
        }
        $version = eZContentObjectVersion::fetchVersion( $parameters['version'], $parameters['object_id'] );
        $attributes = $version->contentObjectAttributes();

        foreach ( $attributes as $attribute )
        {
            $datatype = $attribute->dataType();
            $object = false;
            $objectVersion = false;
            $objectLanguage = false;

            if ( $attribute->hasStoredFileInformation( $object, $objectVersion, $objectLanguage ) )
            {
                $info = $attribute->storedFileInformation( $object, $objectVersion, $objectLanguage );
                $result = Antivirus::hasNoVirus( $info['filepath'] );
                if ( $result === false )
                {
                    $datatype->deleteStoredObjectAttribute( $attribute, $attribute->attribute( "version" ) );
                    $parameters = array( 'info' => $info, 'ip' => $_SERVER['REMOTE_ADDR'], 'object' => $object, 'user' => $user );
                    AntiVirusType::sendMail( 'design:antivirus/email.tpl', $parameters );
                    $process->Template = array( 'templateName' => 'design:antivirus/virus_found.tpl',
                                    'templateVars' => array(  ),
                                    'path' => array( array( 'url' => false, 'text' => 'Anti virus check' ) )
                                  );
                    return eZWorkflowEventType::STATUS_FETCH_TEMPLATE_REPEAT;
                }
            }
        }
        return eZWorkflowEventType::STATUS_ACCEPTED;
    }
    function sendMail( $template, $params = array() )
    {
        $ini = eZINI::instance();

    	$mail = new eZMail();

        $mail->setSender( $ini->variable( 'MailSettings', 'AdminEmail' ) );
        $mail->setReceiver( $ini->variable( 'MailSettings', 'AdminEmail' ) );

        include_once( 'kernel/common/template.php' );
        // fetch text from mail template
        $mailtpl = eZTemplate::factory();
        if( !isset( $params['subject'] ) )
            $params['subject'] = "Virus warning";
        foreach ( $params as $key => $value )
        {
            $mailtpl->setVariable( $key, $value );
        }
        
         
        $mailtext = $mailtpl->fetch( $template );
        $subject = $mailtpl->variable( 'subject' );
        $mail->setSubject( $subject );
        $mail->setBody( $mailtext );

        // mail was sent ok
        if ( eZMailTransport::send( $mail ) )
        {
            return true;
        }
        else
        {
            eZDebug::writeError( "Failed to send mail." );
            return false;
        }
    }
}

eZWorkflowEventType::registerEventType( AntivirusType::WORKFLOW_TYPE_STRING, "antivirustype" );

?>
