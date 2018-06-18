<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    *                                                                        *
    *   @author Prefeitura Municipal de ItajaÃ­                              *
    *   @updated 29/03/2007                                                  *
    *   Pacote: i-PLB Software PÃºblico Livre e Brasileiro                   *
    *                                                                        *
    *   Copyright (C) 2006  PMI - Prefeitura Municipal de ItajaÃ­            *
    *                       ctima@itajai.sc.gov.br                           *
    *                                                                        *
    *   Este  programa  Ã©  software livre, vocÃª pode redistribuÃ­-lo e/ou  *
    *   modificÃ¡-lo sob os termos da LicenÃ§a PÃºblica Geral GNU, conforme  *
    *   publicada pela Free  Software  Foundation,  tanto  a versÃ£o 2 da    *
    *   LicenÃ§a   como  (a  seu  critÃ©rio)  qualquer  versÃ£o  mais  nova.     *
    *                                                                        *
    *   Este programa  Ã© distribuÃ­do na expectativa de ser Ãºtil, mas SEM  *
    *   QUALQUER GARANTIA. Sem mesmo a garantia implÃ­cita de COMERCIALI-    *
    *   ZAÃÃO  ou  de ADEQUAÃÃO A QUALQUER PROPÃSITO EM PARTICULAR. Con-     *
    *   sulte  a  LicenÃ§a  PÃºblica  Geral  GNU para obter mais detalhes.   *
    *                                                                        *
    *   VocÃª  deve  ter  recebido uma cÃ³pia da LicenÃ§a PÃºblica Geral GNU     *
    *   junto  com  este  programa. Se nÃ£o, escreva para a Free Software    *
    *   Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA     *
    *   02111-1307, USA.                                                     *
    *                                                                        *
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
require_once('include/clsBase.inc.php');
require_once('include/clsDetalhe.inc.php');
require_once('include/clsBanco.inc.php');
require_once('include/pmieducar/geral.inc.php');

class clsIndexBase extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo("{$this->_instituicao} i-Educar - Motivo Abandono");
        $this->processoAp = '950';
        $this->addEstilo('localizacaoSistema');
    }
}

class indice extends clsDetalhe
{
    /**
     * Titulo no topo da pagina
     *
     * @var int
     */
    public $titulo;

    public $cod_abandono_tipo;
    public $ref_usuario_exc;
    public $ref_usuario_cad;
    public $nome;
    public $data_cadastro;
    public $data_exclusao;
    public $ativo;
    public $ref_cod_instituicao;

    public function Gerar()
    {
        @session_start();
        $this->pessoa_logada = $_SESSION['id_pessoa'];
        session_write_close();

        $this->titulo = 'Abandono Tipo - Detalhe';

        $this->cod_abandono_tipo=$_GET['cod_abandono_tipo'];

        $tmp_obj = new clsPmieducarAbandonoTipo($this->cod_abandono_tipo);
        $registro = $tmp_obj->detalhe();

        if (! $registro) {
            header('location: educar_abandono_tipo_lst.php');
            die();
        }
        if (class_exists('clsPmieducarInstituicao')) {
            $obj_instituicao = new clsPmieducarInstituicao($registro['ref_cod_instituicao']);
            $obj_instituicao_det = $obj_instituicao->detalhe();
            $registro['ref_cod_instituicao'] = $obj_instituicao_det['nm_instituicao'];
        } else {
            $cod_instituicao = 'Erro na gera&ccedil;&atilde;o';
            echo "<!--\nErro\nClasse n&atilde;o existente: clsPmieducarInstituicao\n-->";
        }

        $obj_permissoes = new clsPermissoes();
        $nivel_usuario = $obj_permissoes->nivel_acesso($this->pessoa_logada);
        if ($nivel_usuario == 1) {
            if ($registro['ref_cod_instituicao']) {
                $this->addDetalhe([ 'Institui&ccedil;&atilde;o', "{$registro['ref_cod_instituicao']}"]);
            }
        }
        if ($registro['nome']) {
            $this->addDetalhe([ 'Motivo Abandono', "{$registro['nome']}"]);
        }
        if ($obj_permissoes->permissao_cadastra(950, $this->pessoa_logada, 7)) {
            $this->url_novo = 'educar_abandono_tipo_cad.php';
            $this->url_editar = "educar_abandono_tipo_cad.php?cod_abandono_tipo={$registro['cod_abandono_tipo']}";
        }
        $this->url_cancelar = 'educar_abandono_tipo_lst.php';
        $this->largura = '100%';

        $localizacao = new LocalizacaoSistema();
        $localizacao->entradaCaminhos([
         $_SERVER['SERVER_NAME'].'/intranet' => 'In&iacute;cio',
         'educar_index.php'                  => 'Escola',
         ''                                  => 'Detalhe do tipo de abandono'
    ]);
        $this->enviaLocalizacao($localizacao->montar());
    }
}

// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm($miolo);
// gera o html
$pagina->MakeAll();
