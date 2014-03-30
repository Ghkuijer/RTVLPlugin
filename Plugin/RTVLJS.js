function deleteRTVLProg(id,link) {
	if(confirm("Bent u zeker dat u het programma met " + id + " wilt verwijderen?")) {
		location.href = "admin.php?page=RTVL_Manage_Programs&delete=" + id;
	}
}

function editRTVLProg(id,link) {
	location.href = "admin.php?page=RTVL_Manage_Programs&edit=" +id;
}

function openLivestream() {
	var parameters = 'width=' + parseInt(popupVars.width) + ',height=' + parseInt(popupVars.height) +',screenX=' + parseInt(popupVars.xPos) + ',screenY=' + parseInt(popupVars.yPos) + ',fullscreen=no,resizable=no,scrollbars=no,menubar=no,toolbar=no';
	window.open(popupVars.url,'Lansingerland%20FM%20Livestream',parameters,'true');
}

function openTVstream() {
	var parameters = 'width=' + parseInt(popupTVVars.width) + ',height=' + parseInt(popupTVVars.height) +',screenX=' + parseInt(popupTVVars.xPos) + ',screenY=' + parseInt(popupTVVars.yPos) + ',fullscreen=no,resizable=no,scrollbars=no,menubar=no,toolbar=no';
	window.open(popupTVVars.url,'Lansingerland%20TV%20Livestream',parameters,'true');
}

function refreshNowPlaying()
{
$('#reloadnp').hide().load('http://www.rtvlansingerland.nl/streams/rtvll.php').show();
}
window.onload=refreshNowPlaying();
var auto_refreshartist = setInterval(refreshNowPlaying, 1000);