<?php

require_once __DIR__.'/../config.php';

$action = GETPOST('action');


if($action == 'confirm_migration')
{
	$PDOdb = new TPDOdb;
	$PDOdb->debug = true;

	echo '<p>Début de la migration</p>';

	$TTablesToRename = array(
		'asset' => array('newname' => 'assetatm', 'fieldtocheck' => 'point_chute')
		, 'asset_type' => array('newname' => 'assetatm_type', 'fieldtocheck' => 'point_chute')
		, 'asset_link' => array('newname' => 'assetatm_link')
		, 'asset_field' => array('newname' => 'assetatm_field')
		, 'assetlot' => array('newname' => 'assetatmlot')
		, 'asset_stock' => array('newname' => 'assetatm_stock')
	);

	foreach($TTablesToRename as $oldname => $TNewTable)
	{
		echo '<p>Renommage de la table <code>'.$oldname.'</code> en <code>'.$TNewTable['newname'].'</code>...</p>';

		$todo = true;

		if(! empty($TNewTable['fieldtocheck']))
		{
			$sql = 'DESCRIBE ' . MAIN_DB_PREFIX . $oldname . ' ' . $TNewTable['fieldtocheck'];
			$TFields = $PDOdb->ExecuteAsArray($sql);

			$todo = count($TFields) > 0;
		}

		if(! $todo)
		{
			echo '<p>Abandon du renommage de la table <code>'.$oldname.'</code> : elle ne contient pas les champs attendus</p>';
			continue;
		}

		$sql = 'RENAME TABLE ' . MAIN_DB_PREFIX . $oldname . ' TO ' . MAIN_DB_PREFIX . $TNewTable['newname'];
		$resql = $PDOdb->Execute($sql);

		if($resql)
		{
			echo '<p>Renommage OK</p>';
		}
	}

	dol_include_once('/core/lib/admin.lib.php');

	echo '<p>Désactivation du module <code>assetatm</code> (suppression des menus et des définitions des droits)...</p>';

	unActivateModule('modAssetatm', 0);

	echo '<p>Réactivation du module <code>assetatm</code> (insertion des menus et des définitions des droits)...</p>';

	activateModule('modAssetatm', 0);
}
elseif($action == 'no_migration')
{
	echo '<p>Pas de migration : vous allez être redirigé(e) vers l\'accueil de Dolibarr <p>';
	echo '<script>setTimeout(function() { document.location.href = "'.dol_buildpath('/', 1).'"; }, 3000);</script>';
}
else
{
?>
<h1>Migration <code>asset</code> pour Dolibarr 8.0</h1>

<p>En vue d'une montée de version de Dolibarr en 8.0, le module <code>asset</code> doit être renommé en <code>assetatm</code>. Les points suivants doivent alors être vérifiés :</p>

<ul>
	<li>Le module <code>asset</code> doit avoir été désactivé AVANT d'avoir été pullé, de manière à éliminer proprement les configurations, entrées menu, etc. Si ce n'a pas été fait, il faut reset avant le renommage.</li>
	<li>Les modules utilisant <code>asset</code> (<code>of</code>, <code>workstation</code>, <code>dispatch</code>, etc., y compris tout module client ayant des développements spécifiques) doivent avoir été pullés sur leur dernière branche stable. Les modules GPAO ont été modifiés pour définir la constante <code>ATM_ASSET_NAME</code> dans le <code>config.default.php</code>. Si cette constante n'est pas disponible, la définir conformément aux autre modules et remplacer les occurrences d'<code>asset</code> (nom de tables, clés des variables <code>$conf</code>, <code>$user->rights</code>, chemin dans <code>dol_include_once()</code>, etc.) par cette constante.</li>
	<li>La présente migration du <code>asset</code> doit être effectuée AVANT la migration Dolibarr. (De toute façon, des conflits auront lieu à la création des tables du module standard.)</li>
	<li>Le répertoire du module doit avoir été renommé en <code>assetatm</code></li>
	<li>Vérifier qu'il n'y a pas d'extrafields de type list issue d'une table qui pointe vers une des tables de asset, si tel est le cas prevoir la modification</li>
</ul>

<p style="text-align:center">Êtes-vous sûr(e) de vouloir procéder à la migration du module <code>asset</code> ?</p>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
	<p style="text-align:center">
		<button type="submit" name="action" value="confirm_migration">Oui</button>
		<span style="display:inline-block;width:40px"></span>
		<button type="submit" name="action" value="no_migration">Non</button>
	</p>
</form>
<?php
}

