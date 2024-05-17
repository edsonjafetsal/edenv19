<?php /* Smarty version 2.6.26, created on 2024-03-27 14:30:41
         compiled from execute.tpl */ ?>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_CALLED']): ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
<!DOCTYPE html>
<HTML>
<HEAD>
<TITLE><?php echo $this->_tpl_vars['TITLE']; ?>
</TITLE>
<?php echo $this->_tpl_vars['OUTPUT_ENCODING']; ?>

</HEAD>
<?php if ($this->_tpl_vars['REPORT_PAGE_STYLE']): ?>
<?php if ($this->_tpl_vars['REPORTICO_STANDALONE_WINDOW']): ?>
<BODY class="swRepBody swRepBodyStandalone" <?php echo $this->_tpl_vars['REPORT_PAGE_STYLE']; ?>
;">
<?php else: ?>
<BODY class="swRepBody">
<?php endif; ?>
<?php else: ?>
<?php if ($this->_tpl_vars['REPORTICO_STANDALONE_WINDOW']): ?>
<BODY class="swRepBody swRepBodyStandalone">
<?php else: ?>
<BODY class="swRepBody">
<?php endif; ?>
<?php endif; ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES']): ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES'] == '2'): ?>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEETDIR']; ?>
/bootstrap2/bootstrap.min.css">
<?php else: ?>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEETDIR']; ?>
/bootstrap3/bootstrap.min.css">
<?php endif; ?>
<?php endif; ?>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php else: ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES']): ?>
<?php if (! $this->_tpl_vars['REPORTICO_BOOTSTRAP_PRELOADED']): ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES'] == '2'): ?>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEETDIR']; ?>
/bootstrap2/bootstrap.min.css">
<?php else: ?>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEETDIR']; ?>
/bootstrap3/bootstrap.min.css">
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEET']; ?>
">
<?php endif; ?>
<?php if ($this->_tpl_vars['AJAX_ENABLED']): ?>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_PRELOADED']): ?>
<?php if (! $this->_tpl_vars['REPORTICO_JQUERY_PRELOADED']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/jquery.js"></script>
<?php endif; ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/reportico.js"></script>
<?php endif; ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES']): ?>
<?php if (! $this->_tpl_vars['REPORTICO_BOOTSTRAP_PRELOADED']): ?>
<?php if ($this->_tpl_vars['BOOTSTRAP_STYLES'] == '2'): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/bootstrap2/bootstrap.min.js"></script>
<?php else: ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/bootstrap3/bootstrap.min.js"></script>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_PRELOADED']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/i18n/jquery.ui.datepicker-<?php echo $this->_tpl_vars['AJAX_DATEPICKER_LANGUAGE']; ?>
.js"></script>
<?php endif; ?>
<?php if (! $this->_tpl_vars['BOOTSTRAP_STYLES']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/jquery.jdMenu.js"></script>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['JSPATH']; ?>
/jquery.jdMenu.css">
<?php endif; ?>
<LINK id="reportico_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['JSPATH']; ?>
/ui/jquery-ui.css">
<script type="text/javascript">var reportico_datepicker_language = "<?php echo $this->_tpl_vars['AJAX_DATEPICKER_FORMAT']; ?>
";</script>
<script type="text/javascript">var reportico_this_script = "<?php echo $this->_tpl_vars['SCRIPT_SELF']; ?>
";</script>
<script type="text/javascript">var reportico_ajax_script = "<?php echo $this->_tpl_vars['REPORTICO_AJAX_RUNNER']; ?>
";</script>
<?php if ($this->_tpl_vars['REPORTICO_BOOTSTRAP_MODAL']): ?>
<script type="text/javascript">var reportico_bootstrap_modal = true;</script>
<?php else: ?>
<script type="text/javascript">var reportico_bootstrap_modal = false;</script>
<?php endif; ?>
<script type="text/javascript">var reportico_ajax_mode = "<?php echo $this->_tpl_vars['REPORTICO_AJAX_MODE']; ?>
";</script>
<script type="text/javascript">var reportico_report_title = "<?php echo $this->_tpl_vars['TITLE']; ?>
";</script>
<script type="text/javascript">var reportico_css_path = "<?php echo $this->_tpl_vars['STYLESHEET']; ?>
";</script>
<?php endif; ?>
<?php if ($this->_tpl_vars['REPORTICO_CHARTING_ENGINE'] == 'FLOT'): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/flot/jquery.flot.axislabels.js"></script>
<?php endif; ?>
<?php if ($this->_tpl_vars['REPORTICO_CHARTING_ENGINE'] == 'NVD3'): ?>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_PRELOADED']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/nvd3/d3.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/nvd3/nv.d3.js"></script>
<LINK id="bootstrap_css" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['JSPATH']; ?>
/nvd3/nv.d3.css">
<?php endif; ?>
<?php endif; ?>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_PRELOADED']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/jquery.dataTables.min.js"></script>
<LINK id="PRP_StyleSheet" REL="stylesheet" TYPE="text/css" HREF="<?php echo $this->_tpl_vars['STYLESHEETDIR']; ?>
/jquery.dataTables.css">
<?php endif; ?>
<?php if ($this->_tpl_vars['PRINTABLE_HTML']): ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['JSPATH']; ?>
/reportico.js"></script>
<script type="text/javascript">
/*
* Where multiple data tables exist due to graphs
* resize the columns of all tables to match the first
*/
function resizeOutputTables(window)
  });

  var tablect = 0;
  reportico_jquery(tableArr).each(function()    });
 });
}
</script>
<?php endif; ?>
<div id="reportico_container">
<div class="swRepForm">
<?php if (strlen ( $this->_tpl_vars['ERRORMSG'] ) > 0): ?>
            <TABLE class="swError">
                <TR>
                    <TD><?php echo $this->_tpl_vars['ERRORMSG']; ?>
</TD>
                </TR>
            </TABLE>
<?php endif; ?>
<?php if (strlen ( $this->_tpl_vars['STATUSMSG'] ) > 0): ?> 
			<TABLE class="swStatus">
				<TR>
					<TD><?php echo $this->_tpl_vars['STATUSMSG']; ?>
</TD>
				</TR>
			</TABLE>
<?php endif; ?>
<?php if ($this->_tpl_vars['SHOW_LOGIN']): ?>
			<TD width="10%"></TD>
			<TD width="55%" align="left" class="swPrpTopMenuCell">
<?php if (strlen ( $this->_tpl_vars['PROJ_PASSWORD_ERROR'] ) > 0): ?>
                                <div style="color: #ff0000;"><?php echo $this->_tpl_vars['PASSWORD_ERROR']; ?>
</div>
<?php endif; ?>
				Enter the report project password. <br><input type="password" name="project_password" value=""></div>
				<input class="swLinkMenu" type="submit" name="login" value="Login">
			</TD>
<?php endif; ?>
<?php if ($this->_tpl_vars['REPORTICO_DYNAMIC_GRIDS']): ?>
<script type="text/javascript">var reportico_dynamic_grids = true;</script>
<?php if ($this->_tpl_vars['REPORTICO_DYNAMIC_GRIDS_SORTABLE']): ?>
<script type="text/javascript">var reportico_dynamic_grids_sortable = true;</script>
<?php else: ?>
<script type="text/javascript">var reportico_dynamic_grids_sortable = false;</script>
<?php endif; ?>
<?php if ($this->_tpl_vars['REPORTICO_DYNAMIC_GRIDS_SEARCHABLE']): ?>
<script type="text/javascript">var reportico_dynamic_grids_searchable = true;</script>
<?php else: ?>
<script type="text/javascript">var reportico_dynamic_grids_searchable = false;</script>
<?php endif; ?>
<?php if ($this->_tpl_vars['REPORTICO_DYNAMIC_GRIDS_PAGING']): ?>
<script type="text/javascript">var reportico_dynamic_grids_paging = true;</script>
<?php else: ?>
<script type="text/javascript">var reportico_dynamic_grids_paging = false;</script>
<?php endif; ?>
<script type="text/javascript">var reportico_dynamic_grids_page_size = <?php echo $this->_tpl_vars['REPORTICO_DYNAMIC_GRIDS_PAGE_SIZE']; ?>
;</script>
<?php else: ?>
<script type="text/javascript">var reportico_dynamic_grids = false;</script>
<?php endif; ?>
<?php echo $this->_tpl_vars['CONTENT']; ?>

</div>
</div>
<?php if (! $this->_tpl_vars['REPORTICO_AJAX_CALLED']): ?>
<?php if (! $this->_tpl_vars['EMBEDDED_REPORT']): ?>
</BODY>
</HTML>
<?php endif; ?>
<?php endif; ?>
