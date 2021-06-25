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
        <a class="nav-link active"  onclick="listHostsTab()" href="#tab1" data-toggle="tab">Hosts</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="ListGivenHostInfo()" href="#tab2" data-toggle="tab">Given Host Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="listGivenHostTriggers()" href="#tab3" data-toggle="tab">Given Host Triggers</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="listAllAlertedTriggers()" href="#tab4" data-toggle="tab">All Alerted Triggers</a>
    </li>
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <div class="table-responsive ZabbixTable table-striped" id="zabbixHostTable"></div> 
    </div>
    <div id="tab2" class="tab-pane">
        <div class="table-responsive ZabbixTable table-striped" id="zabbixGivenHostInfoTable"></div> 
    </div>
    <div id="tab3" class="tab-pane">
        <div id="trigger" class="tab-pane">
            <button class="btn btn-success mb-2" id="triggerCreateButton" onclick="showTriggerCreateModal()" type="button">Create Trigger</button>
            <button class="btn btn-success mb-2" id="problematicTriggersButton" onclick="showListingAlertedTriggersModal()" type="button">Alerted Triggers</button>
        </div>
        <div class="table-responsive ZabbixTable table-striped" id="zabbixGivenHostTriggerInfoTable"></div> 
    </div>
    <div id="tab4" class="tab-pane">
        <div class="table-responsive ZabbixTable table-striped" id="zabbixAllAlertedTriggersTable"></div> 
    </div>
</div>

<script>

    var globalTriggerId;

    function listHostsTab() {
        var form = new FormData();
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
        showSwal('{{__("Yükleniyor...")}}','info',2000);
        
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

    function ListGivenHostInfo() {
        var form = new FormData();
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

    function listGivenHostTriggers() {
        var form = new FormData();
        request(API('listTriggersOfGivenHost'), form, function(response) {
            $('#zabbixGivenHostTriggerInfoTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
            setColorTriggers();
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
            listGivenHostTriggers()
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
        request(API('listProblematicTriggersOfGivenHost'), form, function(response) {
            $('#alertedTriggers-table').find('.table-body').html(response).find("table").DataTable(dataTablePresets('normal'));
            $('#ListingAlertedTriggersModal').find('.modal-title').html('<h4><strong>{{__("Alerted Triggers")}}</strong></h4>');
            $('#ListingAlertedTriggersModal').modal("show");
            setColorGivenHostAlertedTriggers();
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function listAllAlertedTriggers() {
        var form = new FormData();
        request(API('listAllAlertedTriggers'), form, function(response) {
            $('#zabbixAllAlertedTriggersTable').html(response).find('table').DataTable({
            bFilter: true,
            "language" : {
                url : "/turkce.json"
            }
            });;
            setColorAllAlertedTriggers();
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }

    function setColorTriggers() {
        $('#zabbixGivenHostTriggerInfoTable').find("td[id='priority']").each(function(){
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

    function setColorAllAlertedTriggers() {
        $('#zabbixAllAlertedTriggersTable').find("td[id='priority']").each(function(){
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

    function setColorGivenHostAlertedTriggers() {
        $('#alertedTriggers-table').find("td[id='priority']").each(function(){
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
    
</script>