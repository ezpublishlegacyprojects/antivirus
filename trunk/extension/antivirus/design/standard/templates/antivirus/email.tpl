{def $url=concat('http://', ezini( 'SiteSettings', 'SiteURL', 'site.ini' ))}
{set $subject=concat( 'Virus warning from ', $url )}
Dear Webmaster,

We had noticed a virus this file has been already deleted by now.
{$subject}
Yours,
{ezini( 'SiteSettings', 'SiteName', 'site.ini' )}
{$url}