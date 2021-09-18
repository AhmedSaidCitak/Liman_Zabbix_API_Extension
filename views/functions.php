<?php

    function authenticate() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $user = "\"" . extensionDb('user') . "\"";
        $password = "\"" . extensionDb('password') . "\"";

        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"user.login\",
            \"params\": { 
                \"user\": " . $user . ", 
                \"password\": " . $password . " 
            },  
            \"id\": 0 
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $output = json_decode($returnVal,true);
        $auth = $output['result'];

        return $auth;
    }

    function listHosts() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
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
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
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
        $returnVal = runCommand($command);
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
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $hostId = request('hostId');
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"host.get\",
            \"params\": {
                \"filter\": {
                    \"hostid\": [
                        \"" . $hostId . "\"
                    ]
                }
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $informationOfHosts = json_decode($returnVal,true);

        $tableData = [];
        $attrArray = array("hostid", "proxy_hostid", "host", "status", "disable_until", "error", "available", "errors_from", "lastaccess", "ipmi_authtype",
                        "ipmi_privilege", "ipmi_username", "ipmi_password", "ipmi_disable_until", "ipmi_available", "snmp_disable_until", "snmp_available", "maintenanceid", "maintenance_status", "maintenance_type",
                        "maintenance_from", "ipmi_errors_from", "snmp_errors_from", "ipmi_error", "snmp_error", "jmx_disable_until", "jmx_available", "jmx_errors_from", "jmx_error", "name",
                        "flags", "templateid", "description", "tls_connect", "tls_accept", "tls_issuer", "tls_subject", "tls_psk_identity", "tls_psk", "proxy_address", "auto_compress");

        for ($j=0; $j < count($attrArray); $j++) {
            $tableData[] = [
                "attrName" => $attrArray[$j],
                "attrValue" => $informationOfHosts["result"][0][$attrArray[$j]]
            ];
        }
        
        return view('table', [
            "value" => $tableData,
            "title" => ["Host Attributes", "Values"],
            "display" => ["attrName" , "attrValue"],
        ]);

    }

    function givenHostDetailedInfo() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $hostName = request('userGivenHostName');
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"host.get\",
            \"params\": {
                \"filter\": {
                    \"host\": [
                        \"" . $hostName . "\"
                    ]
                }
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $informationOfHosts = json_decode($returnVal,true);

        $tableData = [];
        $attrArray = array("hostid", "proxy_hostid", "host", "status", "disable_until", "error", "available", "errors_from", "lastaccess", "ipmi_authtype",
                        "ipmi_privilege", "ipmi_username", "ipmi_password", "ipmi_disable_until", "ipmi_available", "snmp_disable_until", "snmp_available", "maintenanceid", "maintenance_status", "maintenance_type",
                        "maintenance_from", "ipmi_errors_from", "snmp_errors_from", "ipmi_error", "snmp_error", "jmx_disable_until", "jmx_available", "jmx_errors_from", "jmx_error", "name",
                        "flags", "templateid", "description", "tls_connect", "tls_accept", "tls_issuer", "tls_subject", "tls_psk_identity", "tls_psk", "proxy_address", "auto_compress");

        if(count($informationOfHosts["result"]) == 0) {
            return view('table', [
                "value" => $tableData,
                "title" => ["Host Attributes", "Values"],
                "display" => [],
            ]);
        }

        for ($j=0; $j < count($attrArray); $j++) {
            $tableData[] = [
                "attrName" => $attrArray[$j],
                "attrValue" => $informationOfHosts["result"][0][$attrArray[$j]]
            ];
        }
        
        return view('table', [
            "value" => $tableData,
            "title" => ["Host Attributes", "Values"],
            "display" => ["attrName" , "attrValue"],
        ]);
    }

    function listTriggersOfGivenHost() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $hostName = request('userGivenHostName');
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.get\",
            \"params\": {
                \"host\": \"" . $hostName . "\",
                \"output\": \"extend\",
                \"selectFunctions\": \"extend\"
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $informationOfTriggers = json_decode($returnVal,true);

        $tableData = [];
        $problemSeverity = array("Not classified", "Information", "Warning", "Average", "High", "Disaster");
        $statusCorrespondingValues = array("Enabled", "Disabled");

        for ($i=0; $i < count($informationOfTriggers["result"]); $i++) { 
            $tableData[] = [
                "priority" => $problemSeverity[$informationOfTriggers["result"][$i]["priority"]],
                "triggerid" => $informationOfTriggers["result"][$i]["triggerid"],
                "description" => $informationOfTriggers["result"][$i]["description"],
                "expression" => $informationOfTriggers["result"][$i]["expression"],
                "function" => $informationOfTriggers["result"][$i]["functions"][0]["function"],
                "parameter" => $informationOfTriggers["result"][$i]["functions"][0]["parameter"],
                "status" => $statusCorrespondingValues[$informationOfTriggers["result"][$i]["status"]]
            ];
        }

        return view('table', [
            "value" => $tableData,
            "title" => ["Severity", "Trigger ID", "Description", "Expression", "Function", "Parameter", "Status"],
            "display" => ["priority" , "triggerid", "description", "expression", "function", "parameter", "status"],
            "menu" => [
                "Delete Trigger" => [
                    "target" => "deleteTrigger",
                    "icon" => "fa-trash"
                ],
                "Edit Trigger" => [
                    "target" => "showTriggerEditModal",
                    "icon" => "fa-edit"
                ],
            ],
        ]);
    }

    function deleteTrigger() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $triggerId = request('triggerId');
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.delete\",
            \"params\": [
                \"" . $triggerId . "\"
            ],  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $deleteControl = json_decode($returnVal,true);

        if($deleteControl["result"]["triggerids"][0] == $triggerId)
            return respond("Trigger is successfully deleted",200);
        else
            return respond("Trigger cannot be deleted",400);
    }

    function createTrigger() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $triggerName = request('triggerName');
        $expr = request('expression');
        $priority = request('severityLevel');

        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.create\",
            \"params\": [
                {
                    \"description\": \"" . $triggerName . "\",
                    \"expression\": \"" . $expr . "\",
                    \"priority\": \"" . $priority . "\"
                }
            ],  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $createControl = json_decode($returnVal,true);

//        print_r($createControl);

        if(isset($createControl["result"]["triggerids"][0]))
            return respond("Trigger is successfully created with trigger id = " . $createControl["result"]["triggerids"][0],200);
        else
            return respond("Trigger cannot be created",400);
    }

    function editTrigger() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();
        $triggerId = request('triggerId');
        $severityLevel = request('severityLevel');
        $status = request('status');
        $comment = request('comment');
        
        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.update\",
            \"params\": {
                \"triggerid\": \"" . $triggerId . "\",
                \"status\": \"" . $status . "\",
                \"priority\": \"" . $severityLevel . "\",
                \"comments\": \"" . $comment . "\"
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $editControl = json_decode($returnVal,true);

//        print_r($editControl);

        if($editControl["result"]["triggerids"][0] == $triggerId)
            return respond("Trigger is successfully updated",200);
        else
            return respond("Trigger cannot be updated",400);
    }

    function listProblematicTriggersOfGivenHost() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();

        validate([
            'userGivenHostName' => 'required|string',
        ]);
        $hostName = request('userGivenHostName');

        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.get\",
            \"params\": {
                \"host\": \"" . $hostName . "\",
                \"output\": [
                    \"triggerid\",
                    \"description\",
                    \"priority\"
                ],
                \"filter\": {
                    \"value\": 1
                },
                \"sortfield\": \"priority\",
                \"sortorder\": \"DESC\"
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $informationOfTriggers = json_decode($returnVal,true);

        if($informationOfTriggers["result"] == NULL) {
            $tableData = [];
            return view('table', [
                "value" => $tableData,
                "title" => ["Severity", "Trigger ID", "Description"]
            ]);
        }
        else {
            $problemSeverity = array("Not classified", "Information", "Warning", "Average", "High", "Disaster");

            for ($i=0; $i < count($informationOfTriggers["result"]); $i++) { 
                $tableData[] = [
                    "priority" => $problemSeverity[$informationOfTriggers["result"][$i]["priority"]],
                    "triggerid" => $informationOfTriggers["result"][$i]["triggerid"],
                    "description" => $informationOfTriggers["result"][$i]["description"]
                ];
            }

            return view('table', [
                "value" => $tableData,
                "title" => ["Severity", "Trigger ID", "Description"],
                "display" => ["priority" , "triggerid", "description"],
            ]);
        }
    }

    function listAllAlertedTriggers() {
        $contentType = "'Content-Type: " . extensionDb('contentType') . "'";
        $zabbixServer = extensionDb('zabbixServerIP');
        $zabbixApiUrl = "'http://" . $zabbixServer . "/zabbix/api_jsonrpc.php'";
        $auth = authenticate();

        $data = "'{ 
            \"jsonrpc\": \"2.0\", 
            \"method\": \"trigger.get\",
            \"params\": {
                \"selectHosts\": [
                    \"host\",
                    \"hostid\"
                ],
                \"output\": [
                    \"triggerid\",
                    \"description\",
                    \"priority\"
                ],
                \"filter\": {
                    \"value\": 1
                },
                \"sortfield\": \"priority\",
                \"sortorder\": \"DESC\"
            },  
            \"id\": 1,
            \"auth\":\"" . $auth . "\"
        }'";

        $command = "curl -s -X POST -H " . $contentType . " -d " . $data . " " . $zabbixApiUrl . " | jq '.' ";
        $returnVal = runCommand($command);
        $informationOfTriggers = json_decode($returnVal,true);

        if($informationOfTriggers["result"] == NULL) {
            $tableData = [];
            return view('table', [
                "value" => $tableData,
                "title" => ["Host ID", "Host", "Severity", "Trigger ID", "Description"]
            ]);
        }
        else {
            $problemSeverity = array("Not classified", "Information", "Warning", "Average", "High", "Disaster");

            for ($i=0; $i < count($informationOfTriggers["result"]); $i++) { 
                $tableData[] = [
                    "priority" => $problemSeverity[$informationOfTriggers["result"][$i]["priority"]],
                    "triggerid" => $informationOfTriggers["result"][$i]["triggerid"],
                    "description" => $informationOfTriggers["result"][$i]["description"],
                    "hostid" => $informationOfTriggers["result"][$i]["hosts"][0]["hostid"],
                    "host" => $informationOfTriggers["result"][$i]["hosts"][0]["host"]
                ];
            }

            return view('table', [
                "value" => $tableData,
                "title" => ["Severity", "Host ID", "Host", "Trigger ID", "Description"],
                "display" => ["priority", "hostid", "host", "triggerid", "description"],
            ]);
        }
    }

    function zabbixVersion() {
        $output = runCommand(sudo() . "zabbix_server --version");
        $version = explode("Revision", $output);
        return respond($version[0],200);
    }

    function graphImageById() { 
        //CONFIGURATION
        $z_server = 'http://192.168.1.69//zabbix/';
        $z_user   = 'Admin';
        $z_pass   = 'zabbix';
        $period   = 3600;
        $graphid = request('graphid');
        $width = request('width');
        //NON CONFIGURABLE
        $z_tmp_cookies = "";
        $z_url_index   = $z_server ."index.php";
        $z_url_graph   = $z_server ."chart2.php";
        $z_url_api     = $z_server ."api_jsonrpc.php";

        // Zabbix 2.0
        $z_login_data  = array('name' => $z_user, 'password' => $z_pass, 'enter' => "Sign in");
        
        // file names
        $filename_cookie = $z_tmp_cookies ."zabbix_cookie_" .$graphid .".txt";

        //setup curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $z_url_index);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $z_login_data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $filename_cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $filename_cookie);
        // login
        curl_exec($ch);
        // get graph
        curl_setopt($ch, CURLOPT_URL, $z_url_graph ."?graphid=" .$graphid ."&width=" .$width ."&period=" .$period);
        $output = curl_exec($ch);
        $graphid = 836;
        curl_setopt($ch, CURLOPT_URL, $z_url_graph ."?graphid=" .$graphid ."&width=" .$width ."&period=" .$period);
        $output2 = curl_exec($ch);
        curl_close($ch);
        // delete cookie
//        unlink($filename_cookie);
        
        $image1 = 'data:image/' . "png" . ';base64,' . base64_encode($output);
        $image2 = 'data:image/' . "png" . ';base64,' . base64_encode($output2);
        $images = array("image1" => $image1, "image2" => $image2);
        return respond($images,200);

//        return 'data:image/' . "png" . ';base64,' . base64_encode($output);

    }

?>
