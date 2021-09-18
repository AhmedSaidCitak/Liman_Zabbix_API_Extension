<h1>{{ __('Zabbix Information!') }}</h1>

@component('modal-component',[
    "id" => "hostDetailedInfoModal"
])
<div id="hostInfo-table" class="table-content">
    <div class="table-body"> </div>
</div>
@endcomponent

@component('modal-component',[
    "id" => "ListingAlertedTriggersModal"
])
<div id="alertedTriggers-table" class="table-content">
    <div class="table-body"> </div>
</div>
@endcomponent

@component('modal-component',[
    "id" => "TriggerCreateModal",
    "title" => "Trigger Properties",
    "footer" => [
        "text" => "Create",
        "class" => "btn-success",
        "onclick" => "createTrigger()"
    ]
])
@include('inputs', [
    "inputs" => [
        "Name" => "triggerName:text",
        "Expression" => "expression:text:format: {<server>:<key>.<function>(<parameter>)}<operator><constant>",
        "Severity Level:severityLevel" => [
                "Not Classified" => "0",
                "Information" => "1",
                "Warning" => "2",
                "Average" => "3",
                "High" => "4",
                "Disaster" => "5",
            ]
    ]
])
@endcomponent

@component('modal-component',[
    "id" => "TriggerEditModal",
    "title" => "Trigger Properties",
    "footer" => [
        "text" => "Edit",
        "class" => "btn-success",
        "onclick" => "editTrigger()"
    ]
])
@include('inputs', [
    "inputs" => [
        "Severity Level:severityLevel" => [
                "Not Classified" => "0",
                "Information" => "1",
                "Warning" => "2",
                "Average" => "3",
                "High" => "4",
                "Disaster" => "5",
        ],
        "Status:status" => [
                "Enabled" => "0",
                "Disabled" => "1",
        ],
        "Comment" => "comment:text"
    ]
])
@endcomponent

<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="listHostsTab()" href="#tab1" data-toggle="tab">
        <i class="fas fa-server mr-2"></i>
        {{__('Hosts')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="ListGivenHostInfo()" href="#tab2" data-toggle="tab">
        <i class="fas fa-info-circle mr-2"></i>
        {{__('Info')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="listGivenHostTriggers()" href="#tab3" data-toggle="tab">
        <i class="far fa-bell mr-2"></i>
        {{__('Triggers')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="listAllAlertedTriggers()" href="#tab4" data-toggle="tab">
        <i class="fas fa-bell mr-2"></i>
        {{__('All Alerted Triggers')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="zabbixGraph()" href="#tab6" data-toggle="tab">
        <i class="fas fa-chart-bar mr-2"></i>
        {{__('Zabbix Graph')}}</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="zabbixVersion()" href="#tab5" data-toggle="tab">
        <i class="fas fa-map-marker mr-2"></i>
        {{__('Zabbix Version')}}</a>
    </li>
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <div class="table-responsive ZabbixTable table-striped " id="zabbixHostTable"></div> 
    </div>
    <div id="tab2" class="tab-pane">
        <div class="input-group mb-2">
            <input id="hostNameForInfo" type="text" class="form-control" placeholder="Host Name" aria-label="Host Name" aria-describedby="button-addon1">
            <button class="btn btn-primary" id="button-addon1" onclick="takeHostNameListInfo()" type="button">List Info</button>
        </div>
        <div class="table-responsive ZabbixTable table-striped" id="zabbixGivenHostInfoTable"></div> 
    </div>
    <div id="tab3" class="tab-pane">
        <div id="trigger" class="tab-pane">
            <button class="btn btn-success mb-2" id="triggerCreateButton" onclick="showTriggerCreateModal()" type="button">Create Trigger</button>
            <button class="btn btn-success mb-2" id="problematicTriggersButton" onclick="showListingAlertedTriggersModal()" type="button">Alerted Triggers</button>
        </div>
        <div class="input-group mb-3">
            <input id="hostNameForTriggers" type="text" class="form-control" placeholder="Host Name" aria-label="Host Name" aria-describedby="button-addon2">
            <button class="btn btn-primary" id="button-addon2" onclick="takeHostNameListTriggers()" type="button">List Triggers</button>
        </div>
        <div class="table-responsive ZabbixTable table-striped" id="zabbixGivenHostTriggerInfoTable"></div> 
    </div>
    <div id="tab4" class="tab-pane">
        <div class="table-responsive ZabbixTable table-striped" id="zabbixAllAlertedTriggersTable"></div> 
    </div>
    <div id="tab5" class="tab-pane">
        <div class="card" style="width: 18rem;">
            <div id="zabbixVersion" class="card-body">
            </div>
        </div>
    </div>
    <div id="tab6" class="tab-pane">
        <button class="btn btn-success" id="button-refresh" onclick="refreshGraph()" type="button">Refresh</button>
        <div id="deneme" class="card-body">
        <div class="graph" style="width: 18rem;">
            <img src="{{  API('graphImageById') }}" >
        </div>
        <div class="graph2" style="width: 18rem;">
            <img src="{{  API('graphImageById') }}" >
        </div>
    </div>
</div>

<script>

    if(location.hash === ""){
        listHostsTab();
    }

    var globalTriggerId;
    var userGivenHostNameForTriggers = "";
    var userGivenHostNameForInfo;

    // *** TAB #1 HOSTS ***

    function listHostsTab() {
        var form = new FormData();
        console.log("girdi");
        request(API('listHosts'), form, function(response) {
            $('#zabbixHostTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function serverUptimeInfo(line) {
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        
        var form = new FormData();
        let hostId = line.querySelector("#hostId").innerHTML;
        form.append("hostId", hostId);

        request(API('serverUptimeInfo'), form, function(response) {
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 5000);
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function showHostDetailedInfoModal(line) {
        showSwal('{{__("Yükleniyor...")}}','info',1000);
        
        var form = new FormData();
        let hostName = line.querySelector("#host").innerHTML;
        let hostId = line.querySelector("#hostId").innerHTML;
        form.append("hostId", hostId);

        request(API('hostDetailedInfo'), form, function(response) {
            $('#hostInfo-table').find('.table-body').html(response).find("table").DataTable(dataTablePresets('normal'));
            $('#hostDetailedInfoModal').find('.modal-title').html('<h4><strong>' + hostName + ' - {{__("Detailed Host Information")}}</strong></h4>');
            $('#hostDetailedInfoModal').modal("show");

        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    // *** TAB #2 GIVEN HOST INFO

    function ListGivenHostInfo() {
        var form = new FormData();
        form.append("userGivenHostName", userGivenHostNameForInfo);
        request(API('givenHostDetailedInfo'), form, function(response) {
            $('#zabbixGivenHostInfoTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function takeHostNameListInfo() {
        userGivenHostNameForInfo = document.getElementById("hostNameForInfo").value;
        ListGivenHostInfo();
    }

    // *** TAB #3 GIVEN HOST TRIGGERS

    function listGivenHostTriggers() {
        var form = new FormData();
        form.append("userGivenHostName", userGivenHostNameForTriggers);
        request(API('listTriggersOfGivenHost'), form, function(response) {
            $('#zabbixGivenHostTriggerInfoTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
            setColor("zabbixGivenHostTriggerInfoTable");
            setBadges();
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function deleteTrigger(line) {
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        var form = new FormData();
        let triggerId = line.querySelector("#triggerid").innerHTML;
        form.append("triggerId", triggerId);

        request(API('deleteTrigger'), form, function(response) {
            listGivenHostTriggers();
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 5000);
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function showTriggerCreateModal() {
        $('#TriggerCreateModal').modal("show");
    }

    function createTrigger() {
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        var form = new FormData();
        let triggerName = $('#TriggerCreateModal').find('input[name=triggerName]').val();
        let expression = $('#TriggerCreateModal').find('input[name=expression]').val();
        let severityLevel = $('#TriggerCreateModal').find('select[name=severityLevel]').val();
        form.append("triggerName", triggerName);
        form.append("expression", expression);
        form.append("severityLevel", severityLevel);

        request(API('createTrigger'), form, function(response) {
            listGivenHostTriggers();
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 5000);
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function showTriggerEditModal(line) {
        $('#TriggerEditModal').modal("show");
        globalTriggerId = line.querySelector("#triggerid").innerHTML;
    }

    function editTrigger(line) {
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        var form = new FormData();
        let severityLevel = $('#TriggerEditModal').find('select[name=severityLevel]').val();
        let status = $('#TriggerEditModal').find('select[name=status]').val();
        let comment = $('#TriggerEditModal').find('input[name=comment]').val();
        form.append("triggerId", globalTriggerId);
        form.append("severityLevel", severityLevel);
        form.append("status", status);
        form.append("comment", comment);

        request(API('editTrigger'), form, function(response) {
            listGivenHostTriggers();
            $('#TriggerEditModal').modal("hide");
            $('#TriggerEditModal').find('select[name=severityLevel]').val("Not Classified");
            $('#TriggerEditModal').find('select[name=status]').val("status['Enabled']");
            $("#TriggerEditModal").find('input[name=comment]').val("");
            /*
            document.getElementById("comment").value = "";
            document.getElementById("status").value = "Enabled";
            */
            message = JSON.parse(response)["message"];
            showSwal(message, 'success', 5000);
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function showListingAlertedTriggersModal() {
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        var form = new FormData();
        var variable = userGivenHostNameForTriggers;
        form.append("userGivenHostName", variable);
        request(API('listProblematicTriggersOfGivenHost'), form, function(response) {
            $('#alertedTriggers-table').find('.table-body').html(response).find("table").DataTable(dataTablePresets('normal'));
            $('#ListingAlertedTriggersModal').find('.modal-title').html('<h4><strong>{{__("Alerted Triggers")}}</strong></h4>');
            $('#ListingAlertedTriggersModal').modal("show");
            setColor("alertedTriggers-table");
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function takeHostNameListTriggers() {
        userGivenHostNameForTriggers = document.getElementById("hostNameForTriggers").value;
        listGivenHostTriggers();
    }

    // *** TAB #4 ALL ALERTED TRIGGERS

    function listAllAlertedTriggers() {
        var form = new FormData();
        request(API('listAllAlertedTriggers'), form, function(response) {
            $('#zabbixAllAlertedTriggersTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
            setColor("zabbixAllAlertedTriggersTable");
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    // *** TAB #5 ZABBIX VERSION ***

    function zabbixVersion() {
        var form = new FormData();
        request(API('zabbixVersion'), form, function(response) {
            message = JSON.parse(response)["message"];
            $('#zabbixVersion').html(message);
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    // *** TAB #6 ZABBIX GRAPH

    function zabbixGraph() {
        var form = new FormData();
        form.append('graphid', 847);
        form.append('width', 800);
        form.append('height', 800);
        request(API('graphImageById'), form, function(response) {
//            $('#deneme').html(response);
            message = JSON.parse(response)["message"];
            $(".graph").find("img").attr("src", message["image1"]);
            $(".graph2").find("img").attr("src", message["image2"]);

        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function refreshGraph() {
        zabbixGraph();
    }

    function setColor(id) {
        $("#" + id).find("td[id='priority']").each(function(){
            if($(this).text() == "Not classified"){
                $(this).css('background-color', '#97AAB3');
            }else if($(this).text() == "Information"){
                $(this).css('background-color', '#7499FF');
            }else if($(this).text() == "Warning"){
                $(this).css('background-color', '#FFC859');
            }else if($(this).text() == "Average"){
                $(this).css('background-color', '#FFA059');
            }else if($(this).text() == "High"){
                $(this).css('background-color', '#E97659');
            }else{
                $(this).css('background-color', '#E45959');
            }
        });
    }

    function setBadges(){
        $('#zabbixGivenHostTriggerInfoTable').find("td[id='status']").each(function(){
            $(this).addClass("text-center");
            if($(this).text() == "Enabled"){
                $(this).html(`<small class="badge badge-success">{{ __('Enabled')}}</small>`);
            }else{
                $(this).html(`<small class="badge badge-danger">{{ __('Disabled')}}</small>`);
            }
        });
    }

</script>