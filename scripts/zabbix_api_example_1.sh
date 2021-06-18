baseDir=''

zabbixServer='192.168.1.69'

zabbixUsername='Admin'
zabbixPassword='zabbix'

zabbixHostGroup='Zabbix servers'
maintenanceWindowName="Maintenance Window for $zabbixHostGroup"

header='Content-Type:application/json'
zabbixApiUrl="http://$zabbixServer/zabbix/api_jsonrpc.php"

cd $baseDir

function exit_with_error() {
  echo '********************************'
  echo "$errorMessage"
  echo '--------------------------------'
  echo 'INPUT'
  echo '--------------------------------'
  echo "$json"
  echo '--------------------------------'
  echo 'OUTPUT'
  echo '--------------------------------'
  echo "$result"
  echo '********************************'
  exit 1
}


#------------------------------------------------------
# Auth to zabbix
# https://www.zabbix.com/documentation/3.4/manual/api/reference/user/login
#------------------------------------------------------
errorMessage='*ERROR* - Unable to get Zabbix authorization token'
json=`cat user.login.json`
json=${json/USERNAME/$zabbixUsername}
json=${json/PASSWORD/$zabbixPassword}
result=`curl --silent --show-error --insecure --header $header --data "$json" $zabbixApiUrl`
auth=`echo $result | jq '.result'`
if [ $auth == null ]; then exit_with_error; fi
echo "Login successful - Auth ID: $auth"

# Host Information List
errorMessage='*ERROR* - Unable to list Host Information'
json=`cat hosts.get.json`
json=${json/AUTHID/$auth}
result=`curl --silent --show-error --insecure --header $header --data "$json" $zabbixApiUrl`
auth=`echo $result | jq '.result'`
if [ $auth == null ]; then exit_with_error; fi
echo "Host List successful - Auth ID: $auth"