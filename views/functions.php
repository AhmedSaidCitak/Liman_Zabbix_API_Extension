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

?>