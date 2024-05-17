<?php

/* Copyright (C) 2007-2012  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2014-2016  Juanjo Menent       <jmenent@2byte.es>
 * Copyright (C) 2015       Florian Henry       <florian.henry@open-concept.pro>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2017       Nicolas ZABOURI     <info@inovea-conseil.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    bankwire/bankwire.class.php
 * \ingroup bankwire
 * \brief   This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *          Put some comments here
 */
// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
dol_include_once('/importline/class/formimport.class.php');

/**
 * Class ActionsImportline
 *
 * Put here description of your class
 *
 * @see CommonObject
 */
class ActionsImportline {

    function formObjectOptions($parameters, &$object, &$action, $hookmanager) {
        global $conf, $user, $langs, $object, $hookmanager, $db;
        if (in_array('propalcard', explode(':', $parameters['context'])) || in_array('ordercard', explode(':', $parameters['context'])) || in_array('invoicecard', explode(':', $parameters['context']))) {

            $langs->load('importline@importline');
            if($object->fk_statut !=0){
                return 0;
            }
            $url = dol_buildpath('/importline/js/ajax.php', 1);
            $urlsearch = dol_buildpath('/importline/js/ajaxsearch.php', 1);
            $idactual = GETPOST('id');
            if (empty($idactual)) {
                $idactual = GETPOST('facid');
            }
            $form = new Formimport($db);
            $propal = new Propal($db);
            $otherprop = $propal->liste_array(2, 0, 0);
            $html = "";
            $html .= $langs->trans('selectpropal') . "&nbsp;&nbsp; </br>" . $form->select_propal('', 'pid', '', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300', 'style="width: 50%"') . "<br />";
            $html .= $langs->trans('selectorder') . "&nbsp;&nbsp; </br>" . $form->select_order('', 'oid', '', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300', 'style="width: 50%"') . "<br />";
            $html .= $langs->trans('selectinvoice') . "&nbsp;&nbsp; </br>" . $form->select_invoice('', 'iid', '', 'SelectThirdParty', 0, 0, null, 0, 'minwidth300', 'style="width: 50%"') . "<br />";

            $html .= "";
            $type = ucfirst($object->element);

            print '<!-- Modal -->
                    <div class="modal" id="myModale"   tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:none;" title="' . $langs->trans('Module432439Name') . '">
                            <div class="modal-dialog" role="document" >
                                    <div class="modal-content" style="border: none; box-shadow: none">

                                            <div class="border centpercent center" style="padding-bottom:30px">
                                                    ' . $html . '
                                            </div>
                                            <div id="formcontent">
                                            </div>
                                    </div>
                            </div>
                    </div>';

            print"
               <script>
               $(document).ready(function() {
               $('div.fiche div.tabsAction').prepend('<a class=\"butAction\" id=\"importline\" href=\"#\">" . $langs->trans('ImportLineFrom') . "</a>');
                $('#importline').click(function(){
                    $('#myModale').dialog({
                        width:850,
                        height:600,
                        buttons: [
                        {
                          text: 'OK',
                          click: function() {
                            $( this ).dialog('close');
                          }
                        }
                      ],
                    close: function() {
                        window.location.reload();
                    },
                    
                    });

                });
                $('#pid').change(function(){
                id=$('#pid').val();
                    $.post( '$url', { id: id, idactual:$idactual, src:'Propal',dst:'$type'},function(data){
                    $('#formcontent').html(data);
                    });
                });
                $('#oid').change(function(){
                id=$('#oid').val();
                    $.post( '$url', { id: id, idactual:$idactual, src:'Commande',dst:'$type'},function(data){
                    $('#formcontent').html(data);
                    });
                });
                $('#iid').change(function(){
                id=$('#iid').val();
                    $.post( '$url', { id: id, idactual:$idactual, src:'Facture',dst:'$type'},function(data){
                    $('#formcontent').html(data);
                    });
                });
                
               });
               </script>
                ";
        }

        return 0;
    }

}

