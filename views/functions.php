<?php

    function authenticate() {
        $contentType = "'Content-Type: application/json-rpc'";
        $zabbixServer = "192.168.1.69";
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $user = "\"Admin\"";

        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"user.login\",
            \"params\": { 
                \"user\": " . $user . ", 
                \"password\": \"zabbix\" 
            },  
            \"id\": 0 
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand(sudo() . $command);
        $output = json_decode($returnVal,true);
        $auth = $output['result'];
        
        return $auth;
    }

    function listHosts() {
        $contentType = "'Content-Type: application/json-rpc'";
        $zabbixServer = "192.168.1.69";
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"host.get\",
            \"params\": {
                \"output\":[\"host\"]
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";
        
        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $info = json_decode($returnVal,true);
        $tableData = [];

        for ($i=0; $i < count($info["result"]); $i++) {
            $tableData[] = [
                "host" => $info["result"][$i]["host"],
                "hostId" => $info["result"][$i]["hostid"]
            ];
        }
        
        return view('table', [
            "value" => $tableData,
            "title" => ["Host", "Host ID"],
            "display" => ["host" , "hostId"],
            "onclick" => "showHostDetailedInfoModal",
            "menu" => [
                "Host Uptime Info" => [
                    "target" => "serverUptimeInfo",
                    "icon" => "fa-info"
                ],
            ],
        ]);
    }

    function serverUptimeInfo() {  
        $contentType = "'Content-Type: application/json-rpc'";
        $zabbixServer = "192.168.1.69";
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"host.get\",
            \"params\": {
                \"output\":[\"host\"],
                \"selectInventory\": [
                    \"tag\",
                    \"name\"
                ],
                \"searchInventory\": {
                    \"os\": \"Linux\"
                }
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand(sudo() . $command);
        $info = json_decode($returnVal,true);

        $hostId = request('hostId');
        for ($i=0; $i < count($info["result"]) ; $i++) { 
            if($info["result"][$i]["hostid"] == $hostId) {
                $uptime = "Host uptime: " . $info["result"][$i]["inventory"]["tag"];
                return respond($uptime, 200);
            }
        }
        return respond("Cannot get Zabbix server uptime info", 200);
    }

    function hostDetailedInfo() {
        $contentType = "'Content-Type: application/json-rpc'";
        $zabbixServer = "192.168.1.69";
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"host.get\",
            \"params\": {},  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand(sudo() . $command);
        $informationOfHosts = json_decode($returnVal,true);

        $tableData = [];
        $attrArray = array("hostid", "proxy_hostid", "host", "status", "disable_until", "error", "available", "errors_from", "lastaccess", "ipmi_authtype",
                        "ipmi_privilege", "ipmi_username", "ipmi_password", "ipmi_disable_until", "ipmi_available", "snmp_disable_until", "snmp_available", "maintenanceid", "maintenance_status", "maintenance_type",
                        "maintenance_from", "ipmi_errors_from", "snmp_errors_from", "ipmi_error", "snmp_error", "jmx_disable_until", "jmx_available", "jmx_errors_from", "jmx_error", "name",
                        "flags", "templateid", "description", "tls_connect", "tls_accept", "tls_issuer", "tls_subject", "tls_psk_identity", "tls_psk", "proxy_address", "auto_compress");
        
        $hostId = request('hostId');

        for ($i=0; $i < count($informationOfHosts["result"]) ; $i++) { 
            if($informationOfHosts["result"][$i]["hostid"] == $hostId) {
                for ($j=0; $j < count($attrArray); $j++) {
                    $tableData[] = [
                        "attrName" => $attrArray[$j],
                        "attrValue" => $informationOfHosts["result"][$i][$attrArray[$j]]
                    ];
                }
                
                return view('table', [
                    "value" => $tableData,
                    "title" => ["Host Attributes", "Values"],
                    "display" => ["attrName" , "attrValue"],
                ]);
            }
        }
    }

?>