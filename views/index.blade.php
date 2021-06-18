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
</ul>

<div class="tab-content">
    <div id="tab1" class="tab-pane active">
        <div class="table-responsive ZabbixTable" id="zabbixHostTable"></div> 
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

</script>