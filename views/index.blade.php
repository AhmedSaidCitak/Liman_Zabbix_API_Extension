<h1>{{ __('Zabbix Information!') }}</h1>

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

    function showHostDetailedInfoModal() {
        
    }

    getHostname();
    function getHostname(){
        showSwal('{{__("Yükleniyor...")}}', 'info');
        let data = new FormData();
        request("{{API("get_hostname")}}", data, function(response){
            response = JSON.parse(response);
            $('#hostname').text(response.message);
            Swal.close();
            $('#setHostnameModal').modal('hide')
        }, function(response){
            response = JSON.parse(response);
            showSwal(response.message, 'error');
        });
    }
</script>