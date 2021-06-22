<h1>{{ __('Zabbix Information!') }}</h1>

@component('modal-component',[
    "id" => "hostDetailedInfoModal"
])
<div id="hostInfo-table" class="table-content">
    <div class="table-body"> </div>
</div>
@endcomponent


<ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
    <li class="nav-item">
        <a class="nav-link active"  onclick="listHostsTab()" href="#tab1" data-toggle="tab">List Hosts</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="ListGivenHostInfo()" href="#tab2" data-toggle="tab">List Given Host Info</a>
    </li>
    <li class="nav-item">
        <a class="nav-link "  onclick="listGivenHostTriggers()" href="#tab3" data-toggle="tab">List Given Host Triggers</a>
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
        <div class="table-responsive ZabbixTable table-striped" id="zabbixGivenHostTriggerInfoTable"></div> 
    </div>
</div>

<script>

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
        }, function(response) {
            let error = JSON.parse(response);
            showSwal(error.message, 'error', 3000);
        });
    }
</script>