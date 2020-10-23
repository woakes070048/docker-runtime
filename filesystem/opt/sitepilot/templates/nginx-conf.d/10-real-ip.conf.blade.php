# vim: set ft=nginx:

@foreach($nginx['realIpFrom'] as $ip)
set_real_ip_from  {{ $ip }};
@endforeach
real_ip_header    {{ $nginx['realIpHeader'] }};
real_ip_recursive {{ $nginx['realIpRecursive'] }};
