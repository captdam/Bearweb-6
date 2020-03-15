<?php
	//Overall config
	define('URL_PARAM',	'url');
	
	//Database login
	define('DB_HOST',	'localhost');
	define('DB_USERNAME',	'bearweb');
	define('DB_PASSWORD',	'x');
	define('DB_NAME',	'Bearweb');
	
	//Object storgae
	define('OS_USERNAME',	'x');
	define('OS_PASSWORD',	'x');
	define('OS_TENANT',	'x');
	define('OS_PRIVATECONTAINER',	'Bearweb');
	define('OS_PUBLICCONTAINER',	'BearwebPublic');
	
	//Security
	define('FORCEHTTPS',	true);
	
	//SEO
	define('SEO_DOMAIN', array( #When generate sitemap, URLs will be this + url_of_resources. echo: SEO_DOMAIN[SITENAME]."local/url/to/resource.html"
		'captdam.com' => 'https://captdam.com/',
		'beardle.com' => 'https://beardle.com/'
	));
	
	//Debug mode
	define('DEBUGMODE',	true);
?>
