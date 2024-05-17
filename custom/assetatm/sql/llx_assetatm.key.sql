-- ===================================================================
-- Copyright (C) 2006 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2011 Regis Houssin        <regis@dolibarr.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- $Id: llx_assetatm.key.sql,v 1.0 2011/11/09 15:11:41 atm-maxime Exp $
-- ===================================================================



ALTER TABLE llx_assetatm ADD INDEX idx_asset_fk_soc (fk_soc);
ALTER TABLE llx_assetatm ADD INDEX idx_asset_fk_product (fk_product);
ALTER TABLE llx_assetatm ADD INDEX idx_asset_fk_soc (fk_soc);
ALTER TABLE llx_assetatm ADD INDEX idx_asset_fk_affaire (fk_affaire);
ALTER TABLE `llx_assetatm` ADD INDEX idx_asset_entity ( `entity` ) ;


