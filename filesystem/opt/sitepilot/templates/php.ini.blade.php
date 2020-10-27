
log_errors=on
expose_php=off
display_errors=off

sendmail_path=/usr/bin/msmtp -C /opt/sitepilot/etc/msmtp.conf --read-envelope-from -t

apc.serializer=igbinary

session.serialize_handler=igbinary
