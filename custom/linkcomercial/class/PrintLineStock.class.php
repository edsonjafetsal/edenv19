<?php
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';

class PrintLineStock extends Commande
{


	public function printOriginLinesList($restrictlist = '', $selectedLines = array())
	{
		global $langs, $hookmanager, $conf, $form;

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans('Ref').'</td>';
		print '<td>'.$langs->trans('Description').'</td>';
		print '<td class="right">'.$langs->trans('VATRate').'</td>';
		print '<td class="right">'.$langs->trans('PriceUHT').'</td>';
		if (!empty($conf->multicurrency->enabled)) print '<td class="right">'.$langs->trans('PriceUHTCurrency').'</td>';
		print '<td class="right">'.$langs->trans('Qty').'</td>';
		if (!empty($conf->global->PRODUCT_USE_UNITS))
		{
			print '<td class="left">'.$langs->trans('Unit').'</td>';
		}
		print '<td class="right">'.$langs->trans('ReductionShort').'</td>';
		print '<td class="right">'.$langs->trans('PhysicalStock').'</td>';
		print '<td class="right">'.$langs->trans('SupplierRef').'</td>';
		print '<td class="center">'.$form->showCheckAddButtons('checkforselect', 1).'</td>';
		print '</tr>';
		$i = 0;

		if (!empty($this->lines))
		{
			foreach ($this->lines as $line)
			{
				if (is_object($hookmanager) && (($line->product_type == 9 && !empty($line->special_code)) || !empty($line->fk_parent_line)))
				{
					if (empty($line->fk_parent_line))
					{
						$parameters = array('line'=>$line, 'i'=>$i);
						$action = '';
						$hookmanager->executeHooks('printOriginObjectLine', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
					}
				} else {
					$this->printOriginLine($line, '', $restrictlist, '/core/tpl', $selectedLines);
				}

				$i++;
			}
		}
	}

	/**
	 * 	Return HTML with a line of table array of source object lines
	 *  TODO Move this and previous function into output html class file (htmlline.class.php).
	 *  If lines are into a template, title must also be into a template
	 *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
	 *
	 * 	@param	CommonObjectLine	$line				Line
	 * 	@param	string				$var				Var
	 *	@param	string				$restrictlist		''=All lines, 'services'=Restrict to services only (strike line if not)
	 *  @param	string				$defaulttpldir		Directory where to find the template
	 *  @param  array       		$selectedLines      Array of lines id for selected lines
	 * 	@return	void
	 */
	public function printOriginLine($line, $var, $restrictlist = '', $defaulttpldir = '/core/tpl', $selectedLines = array())
	{
		global $langs, $conf;

		//var_dump($line);
		if (!empty($line->date_start))
		{
			$date_start = $line->date_start;
		} else {
			$date_start = $line->date_debut_prevue;
			if ($line->date_debut_reel) $date_start = $line->date_debut_reel;
		}
		if (!empty($line->date_end))
		{
			$date_end = $line->date_end;
		} else {
			$date_end = $line->date_fin_prevue;
			if ($line->date_fin_reel) $date_end = $line->date_fin_reel;
		}

		$this->tpl['id'] = $line->id;

		$this->tpl['label'] = '';
		if (!empty($line->fk_parent_line)) $this->tpl['label'] .= img_picto('', 'rightarrow');

		if (($line->info_bits & 2) == 2)  // TODO Not sure this is used for source object
		{
			$discount = new DiscountAbsolute($this->db);
			$discount->fk_soc = $this->socid;
			$this->tpl['label'] .= $discount->getNomUrl(0, 'discount');
		} elseif (!empty($line->fk_product))
		{
			$productstatic = new Product($this->db);
			$productstatic->id = $line->fk_product;
			$productstatic->ref = $line->ref;
			$productstatic->type = $line->fk_product_type;
			if (empty($productstatic->ref)) {
				$line->fetch_product();
				$productstatic = $line->product;
			}

			$this->tpl['label'] .= $productstatic->getNomUrl(1);
			$this->tpl['label'] .= ' - '.(!empty($line->label) ? $line->label : $line->product_label);
			// Dates
			if ($line->product_type == 1 && ($date_start || $date_end))
			{
				$this->tpl['label'] .= get_date_range($date_start, $date_end);
			}
		} else {
			$this->tpl['label'] .= ($line->product_type == -1 ? '&nbsp;' : ($line->product_type == 1 ? img_object($langs->trans(''), 'service') : img_object($langs->trans(''), 'product')));
			if (!empty($line->desc)) {
				$this->tpl['label'] .= $line->desc;
			} else {
				$this->tpl['label'] .= ($line->label ? '&nbsp;'.$line->label : '');
			}

			// Dates
			if ($line->product_type == 1 && ($date_start || $date_end))
			{
				$this->tpl['label'] .= get_date_range($date_start, $date_end);
			}
		}

		if (!empty($line->desc))
		{
			if ($line->desc == '(CREDIT_NOTE)')  // TODO Not sure this is used for source object
			{
				$discount = new DiscountAbsolute($this->db);
				$discount->fetch($line->fk_remise_except);
				$this->tpl['description'] = $langs->transnoentities("DiscountFromCreditNote", $discount->getNomUrl(0));
			} elseif ($line->desc == '(DEPOSIT)')  // TODO Not sure this is used for source object
			{
				$discount = new DiscountAbsolute($this->db);
				$discount->fetch($line->fk_remise_except);
				$this->tpl['description'] = $langs->transnoentities("DiscountFromDeposit", $discount->getNomUrl(0));
			} elseif ($line->desc == '(EXCESS RECEIVED)')
			{
				$discount = new DiscountAbsolute($this->db);
				$discount->fetch($line->fk_remise_except);
				$this->tpl['description'] = $langs->transnoentities("DiscountFromExcessReceived", $discount->getNomUrl(0));
			} elseif ($line->desc == '(EXCESS PAID)')
			{
				$discount = new DiscountAbsolute($this->db);
				$discount->fetch($line->fk_remise_except);
				$this->tpl['description'] = $langs->transnoentities("DiscountFromExcessPaid", $discount->getNomUrl(0));
			} else {
				$this->tpl['description'] = dol_trunc($line->desc, 60);
			}
		} else {
			$this->tpl['description'] = '&nbsp;';
		}

		// VAT Rate
		$this->tpl['vat_rate'] = vatrate($line->tva_tx, true);
		$this->tpl['vat_rate'] .= (($line->info_bits & 1) == 1) ? '*' : '';
		if (!empty($line->vat_src_code) && !preg_match('/\(/', $this->tpl['vat_rate'])) $this->tpl['vat_rate'] .= ' ('.$line->vat_src_code.')';

		$this->tpl['price'] = price($line->subprice);
		$this->tpl['multicurrency_price'] = price($line->multicurrency_subprice);
		$this->tpl['qty'] = (($line->info_bits & 2) != 2) ? $line->qty : '&nbsp;';
		if (!empty($conf->global->PRODUCT_USE_UNITS)) $this->tpl['unit'] = $langs->transnoentities($line->getLabelOfUnit('long'));
		$this->tpl['remise_percent'] = (($line->info_bits & 2) != 2) ? vatrate($line->remise_percent, true) : '&nbsp;';

		// Is the line strike or not
		$this->tpl['strike'] = 0;
		if ($restrictlist == 'services' && $line->product_type != Product::TYPE_SERVICE) $this->tpl['strike'] = 1;
        $line->stockreal=$productstatic->load_stock('warehouseopen, warehouseinternal', '');
		// Output template part (modules that overwrite templates must declare this into descriptor)
		// Use global variables + $dateSelector + $seller and $buyer
		//$dirtpls = array_merge($conf->modules_parts['tpl'], array($defaulttpldir));
		$this->tpl['fk_product']=$line->fk_product;
		$tpl = DOL_DOCUMENT_ROOT.'/custom/linkcomercial/core/tpl/originproductline.tpl.php';
		include $tpl;
	}

}
class CommandeLine extends Orderline{
	public $stockreal;
	public $vendorsku;
}
