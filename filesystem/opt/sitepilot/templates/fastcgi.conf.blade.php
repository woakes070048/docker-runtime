# vim: set ft=nginx:

fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;

include fastcgi_params;
