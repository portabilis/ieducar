<?php

/**
 * i-Educar - Sistema de gestão escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de Itajaí
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa é software livre; você pode redistribuí-lo e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versão 2 da Licença, como (a seu critério)
 * qualquer versão posterior.
 *
 * Este programa é distribuí­do na expectativa de que seja útil, porém, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implí­cita de COMERCIABILIDADE OU
 * ADEQUAÇÃO A UMA FINALIDADE ESPECÍFICA. Consulte a Licença Pública Geral
 * do GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral do GNU junto
 * com este programa; se não, escreva para a Free Software Foundation, Inc., no
 * endereço 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Eriksen Costa Paixão <eriksen.paixao_bs@cobra.com.br>
 *
 * @category  i-Educar
 * @package   Core_Controller
 *
 * @since     Arquivo disponível desde a versão 1.1.0
 *
 * @version   $Id$
 */

/**
 * Core_Controller_Page_Validatable interface.
 *
 * @author    Eriksen Costa Paixão <eriksen.paixao_bs@cobra.com.br>
 * @author    Caroline Salib <carolinesalibc@gmail.com>
 *
 * @category  i-Educar
 * @package   Core_Controller
 *
 * @since     Interface disponível desde a versão 1.1.0
 *
 * @version   @@package_version@@
 */
interface Core_Controller_Page_Validatable
{
    /**
     * Retorna um array com objetos CoreExt_Validate.
     *
     * @return array
     */
    public function getValidators();
}
