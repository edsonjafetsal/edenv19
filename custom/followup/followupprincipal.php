<?php
$sql = "SELECT * FROM " . MAIN_DB_PREFIX . "actioncomm WHERE fk_soc = " . $socid . " and fk_action!=40 order by datep ASC";
$resql = $db->query($sql);
$events = array();
$eventnext = array();
$iconos = array();

$year= date("Y");
$month= date("m");
$day= date("d");
$hourminsec = date("His");

$tercero=new Societe($db);
$tercero->fetch($socid);

while ($obj = $db->fetch_object($resql)) {
	$actioncom = new ActionComm($db);
	$actioncom->fetch($obj->id);
	//si el evento esta dentro de la semana actual agregar a events
	if ($db->idate($actioncom->datep)<= date("Y-m-d", strtotime("sunday this week"))) {
		$events[] = $actioncom;
	} else {
		$eventnext[] = $actioncom;

}
	switch ($actioncom->type_id) {
		case 1:
			$iconos[$actioncom->type_id] = "fa fa-phone bg-blue";
			break;
		case 2:
			$iconos[$actioncom->type_id] = "fa fa-fax bg-purple";
			break;
		case 4:
			$iconos[$actioncom->type_id] = "fa fa-envelope-o bg-blue";
			break;
		case 5:
			$iconos[$actioncom->type_id] = "fa fa-users bg-red";
			break;
		case 6:
			$iconos[$actioncom->type_id] = "fa fa-envelope bg-green";
			break;
		case 11:
			$iconos[$actioncom->type_id] = "fa fa-chain-broken bg-maroon";
			break;
		case 12:
			$iconos[$actioncom->type_id] = "fa fa-chain bg-maroon";
			break;
		case 50:
			$iconos[$actioncom->type_id] = "fa fa-comments bg-yellow";
			break;
		default:
			$iconos[$actioncom->type_id] = "fa fa-comments bg-yellow";
			break;

	}
//	$iconos[$actioncom->id]= $actioncom->code;
}


?>
<div class="nav-tabs-custom">
	<ul class="nav nav-tabs">
		<li class="active tab_follow"><a href="#tab_follow" data-toggle="tab" aria-expanded="true">Follow Up</a></li>
		<li class="tab_list"><a href="#tab_list" data-toggle="tab" aria-expanded="false">Agenda </a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="tab_follow">
			<section class="content">
				<?php
				if (!$tercero->array_options['options_followupenable']){
					print ' &nbsp; <a class="btn btn-success" id="neweventfollowup"  style="margin-bottom: 20px;" href="'.DOL_URL_ROOT.'/comm/action/card.php?action=create&socid='.$socid.'&backtopage='.urlencode($_SERVER["PHP_SELF"].'?socid='.$socid).
						'&datep='.sprintf("%04d%02d%02d", $year, $month, $day).$hourminsec.'&label='.$tercero->name.' - '.'Meetings'.'">
 <i class="fa fa-plus" id="createevent"> New Event</i>
</a>';
				}

				?>

				<!-- row -->
				<div class="row">
					<div class="col-md-6">
						<div class="alert alert-info">
							<h4><i class="icon fa fa-info"></i> Events in Process</h4>
							Events in process are displayed here.
						</div>
						<!-- The time line -->
						<ul class="timeline">
							<!-- timeline time label -->
							<?php
							foreach ($events as $event) {
								?>
								<li class="time-label">
						<span class="<?php if ($event->datep > dol_now()) {
							echo "bg-green";
						} else {
							echo "bg-red";
						} ?>"><?php echo dol_print_date($event->datep, 'dayrfc'); ?></span>
								</li>
								<li>
									<i class="<?php echo $iconos[$event->type_id]; ?>"></i>

									<div class="timeline-item">
										<span class="time"><i class="fa fa-clock-o"></i> <?php echo dol_print_date($event->datep, 'hour'); ?></span>

										<h3 class="timeline-header"><a href="#"><?php echo $event->author->firstname." - ".$event->author->lastname ;?> </a> Have been created <span class="text-info text-capitalize"><?php echo $event->label; ?></span></h3>

										<div class="timeline-body editable" data-id="<?php echo $event->id; ?>">
											<?php echo $event->note ;?>
										</div>
										<div class="timeline-footer">
											<a class="btn btn-primary btn-xs" target="_blank" href="<?php echo DOL_URL_ROOT . '/comm/action/card.php?id=' . $event->id; ?>">Read more</a>
											<?php
											if(!$tercero->array_options['options_followupenable']){
												?>
												<a class="btn btn-danger btn-xs deleteevent" data-id="<?php echo $event->id; ?>">Delete</a>
												<a class="btn btn-success btn-xs editevent" data-id="<?php echo $event->id; ?>"><i class="fa fa-edit"></i> Comment</a>
												<?php
											}
											?>

										</div>
									</div>
								</li>
								<?php

							}
							?>

						</ul>
					</div>
					<div class="col-md-6">
						<div class="callout callout-warning">
							<h4><i class="icon fa fa-info"></i> Next Events</h4>
							Next Events will be here
						</div>
						<!-- The time line -->
						<ul class="timeline">
							<!-- timeline time label -->
							<?php
							foreach ($eventnext as $event) {
								?>
								<li class="time-label">
						<span class="<?php if ($event->datep > dol_now()) {
							echo "bg-green";
						} else {
							echo "bg-red";
						} ?>"><?php echo dol_print_date($event->datep, 'dayrfc'); ?></span>
								</li>
								<li>
									<i class="<?php echo $iconos[$event->type_id]; ?>"></i>

									<div class="timeline-item">
										<span class="time"><i class="fa fa-clock-o"></i> <?php echo dol_print_date($event->datep, 'hour'); ?></span>

										<h3 class="timeline-header"><a href="#"><?php echo $event->author->firstname." - ".$event->author->lastname ;?> </a> Have been created <span class="text-info text-capitalize"><?php echo $event->label; ?></span></h3>

										<div class="timeline-body editable" data-id="<?php echo $event->id; ?>">
											<?php echo $event->note ;?>
										</div>
										<div class="timeline-footer">
											<a class="btn btn-primary btn-xs" target="_blank" href="<?php echo DOL_URL_ROOT . '/comm/action/card.php?id=' . $event->id; ?>">Read more</a>
											<?php
											if(!$tercero->array_options['options_followupenable']){
												?>
												<a class="btn btn-danger btn-xs deleteevent" data-id="<?php echo $event->id; ?>">Delete</a>
												<a class="btn btn-success btn-xs editevent" data-id="<?php echo $event->id; ?>"><i class="fa fa-edit"></i> Comment</a>
												<?php
											}
											?>

										</div>
									</div>
								</li>
								<?php

							}
							?>

						</ul>
					</div>
					<!-- /.col -->
				</div>
				<!-- /.row -->

				<div class="row" style="margin-top: 10px;">
					<div class="col-md-12">

						<!-- /.box -->
					</div>
					<!-- /.col -->
				</div>
				<!-- /.row -->

			</section>
		</div>
		<!-- /.tab-pane -->

		<div class="tab-pane" id="tab_list" >
			<div class="row">
				<a class="btn btn-app" id="btn_list">
					<i class="fa fa-list"></i> List
				</a>
				<a class="btn btn-app" id="btn_month" style="border: 1px solid #ccc; border-radius: 3px;">
					<i class="fa fa-calendar"></i> Month
				</a>
			</div>
			<div id="listado" style="display: flex"></div>

		</div>

	</div>
	<!-- /.tab-content -->
</div>

