<?php
    defined('C5_EXECUTE') or die("Access Denied.");
    // Create formhelper
    $form = Loader::helper('form');
?>
 
<div id="btHorizon">
    <?php
	echo $form->label('type',"Selectionner la source des publications: ");
	echo $form->select('type',array('Horizon'=>'Horizon','HAL'=>'HAL'),'Horizon');
        echo $form->label('name', "Saisir l'URL à parser pour récupérer les publications, ex Horizon: http://www.documentation.ird.fr/hor/NOM,PRENOM/2011, ou encore http://www.documentation.ird.fr/hor/unite:UR227:tout, ex HAL: https://hal-univ-reunion.archives-ouvertes.fr/ECOMAR/search/index/q/authFullName_t%3A%28Nom+Prenom%29/ :");
        echo $form->text('name', $name);
    ?>
</div>
