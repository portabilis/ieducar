<?php

/**
 * i-Educar - Sistema de gestÃ£o escolar
 *
 * Copyright (C) 2006  Prefeitura Municipal de ItajaÃ­
 *                     <ctima@itajai.sc.gov.br>
 *
 * Este programa Ã© software livre; vocÃª pode redistribuÃ­-lo e/ou modificÃ¡-lo
 * sob os termos da LicenÃ§a PÃºblica Geral GNU conforme publicada pela Free
 * Software Foundation; tanto a versÃ£o 2 da LicenÃ§a, como (a seu critÃ©rio)
 * qualquer versÃ£o posterior.
 *
 * Este programa Ã© distribuÃ­Â­do na expectativa de que seja Ãºtil, porÃ©m, SEM
 * NENHUMA GARANTIA; nem mesmo a garantia implÃ­Â­cita de COMERCIABILIDADE OU
 * ADEQUAÃÃO A UMA FINALIDADE ESPECÃFICA. Consulte a LicenÃ§a PÃºblica Geral
 * do GNU para mais detalhes.
 *
 * VocÃª deve ter recebido uma cÃ³pia da LicenÃ§a PÃºblica Geral do GNU junto
 * com este programa; se nÃ£o, escreva para a Free Software Foundation, Inc., no
 * endereÃ§o 59 Temple Street, Suite 330, Boston, MA 02111-1307 USA.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 *
 * @category  i-Educar
 *
 * @license   @@license@@
 *
 * @package   iEd_Pmieducar
 *
 * @since     Arquivo disponÃ­vel desde a versÃ£o 1.0.0
 *
 * @version   $Id$
 */

require_once 'include/clsBase.inc.php';
require_once 'include/clsCadastro.inc.php';
require_once 'include/clsBanco.inc.php';
require_once 'include/pmieducar/geral.inc.php';
require_once 'ComponenteCurricular/Model/ComponenteDataMapper.php';

/**
 * clsIndexBase class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 *
 * @category  i-Educar
 *
 * @license   @@license@@
 *
 * @package   iEd_Pmieducar
 *
 * @since     Classe disponÃ­vel desde a versÃ£o 1.0.0
 *
 * @version   @@package_version@@
 */
class clsIndexBase extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo($this->_instituicao . ' i-Educar - Dispensa Componente Curricular');
        $this->processoAp = 578;
        $this->addEstilo('localizacaoSistema');
    }
}

/**
 * indice class.
 *
 * @author    Lucas Schmoeller da Silva <lucas@portabilis.com.br>
 *
 * @category  i-Educar
 *
 * @license   @@license@@
 *
 * @package   iEd_Pmieducar
 *
 * @since     Classe disponÃ­vel desde a versÃ£o 1.0.0
 *
 * @version   @@package_version@@
 */
class indice extends clsCadastro
{
    public $pessoa_logada;

    public $observacao;

    public $ref_cod_matricula;
    public $ref_cod_turma;
    public $ref_cod_serie;
    public $ref_cod_disciplina;
    public $ref_sequencial;
    public $ref_cod_instituicao;
    public $ref_cod_escola;

    public function Inicializar()
    {
        $retorno = 'Novo';
        @session_start();
        $this->pessoa_logada = $_SESSION['id_pessoa'];
        @session_write_close();

        $this->ref_cod_disciplina = $_GET['ref_cod_disciplina'];
        $this->ref_cod_matricula = $_GET['ref_cod_matricula'];

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(
            578,
            $this->pessoa_logada,
            7,
            'educar_disciplina_dependencia_lst.php?ref_ref_cod_matricula=' . $this->ref_cod_matricula
        );

        if (is_numeric($this->ref_cod_matricula)) {
            $obj_matricula = new clsPmieducarMatricula(
                $this->ref_cod_matricula,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                1
            );

            $det_matricula = $obj_matricula->detalhe();

            if (!$det_matricula) {
                header('Location: educar_matricula_lst.php');
                die();
            }

            $this->ref_cod_escola = $det_matricula['ref_ref_cod_escola'];
            $this->ref_cod_serie = $det_matricula['ref_ref_cod_serie'];
        } else {
            header('Location: educar_matricula_lst.php');
            die();
        }

        if (is_numeric($this->ref_cod_matricula) && is_numeric($this->ref_cod_serie) &&
            is_numeric($this->ref_cod_escola) && is_numeric($this->ref_cod_disciplina)
        ) {
            $obj = new clsPmieducarDisciplinaDependencia(
                $this->ref_cod_matricula,
                $this->ref_cod_serie,
                $this->ref_cod_escola,
                $this->ref_cod_disciplina
            );

            $registro = $obj->detalhe();

            if ($registro) {
                // passa todos os valores obtidos no registro para atributos do objeto
                foreach ($registro as $campo => $val) {
                    $this->$campo = $val;
                }

                $obj_permissoes = new clsPermissoes();

                if ($obj_permissoes->permissao_excluir(578, $this->pessoa_logada, 7)) {
                    $this->fexcluir = true;
                }

                $retorno = 'Editar';
            }
        }

        $this->url_cancelar = $retorno == 'Editar' ?
            sprintf(
                'educar_disciplina_dependencia_det.php?ref_cod_matricula=%d&ref_cod_serie=%d&ref_cod_escola=%d&ref_cod_disciplina=%d',
                $registro['ref_cod_matricula'],
                $registro['ref_cod_serie'],
                $registro['ref_cod_escola'],
                $registro['ref_cod_disciplina']
            ) :
            'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula;

        $this->nome_url_cancelar = 'Cancelar';

        $localizacao = new LocalizacaoSistema();
        $localizacao->entradaCaminhos([
            $_SERVER['SERVER_NAME'] . '/intranet' => 'In&iacute;cio',
            'educar_index.php' => 'Escola',
            '' => 'Disciplinas de dependência'
        ]);
        $this->enviaLocalizacao($localizacao->montar());

        return $retorno;
    }

    public function Gerar()
    {
        /**
         * Busca dados da matricula
         */
        $obj_ref_cod_matricula = new clsPmieducarMatricula();
        $detalhe_aluno = array_shift($obj_ref_cod_matricula->lista($this->ref_cod_matricula));

        $obj_aluno = new clsPmieducarAluno();
        $det_aluno = array_shift($det_aluno = $obj_aluno->lista(
            $detalhe_aluno['ref_cod_aluno'],
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            1
        ));

        $obj_escola = new clsPmieducarEscola(
            $this->ref_cod_escola,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            1
        );

        $det_escola = $obj_escola->detalhe();
        $this->ref_cod_instituicao = $det_escola['ref_cod_instituicao'];

        $obj_matricula_turma = new clsPmieducarMatriculaTurma();
        $lst_matricula_turma = $obj_matricula_turma->lista(
            $this->ref_cod_matricula,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            1,
            $this->ref_cod_serie,
            null,
            $this->ref_cod_escola
        );

        if (is_array($lst_matricula_turma)) {
            $det = array_shift($lst_matricula_turma);
            $this->ref_cod_turma = $det['ref_cod_turma'];
            $this->ref_sequencial = $det['sequencial'];
        }

        $this->campoRotulo('nm_aluno', 'Nome do Aluno', $det_aluno['nome_aluno']);

        if (!isset($this->ref_cod_turma)) {
            $this->mensagem = 'Para cadastrar uma disciplina de depend&ecirc;ncia de um aluno, &eacute; necess&aacute;rio que este esteja enturmado.';

            return;
        }

        // primary keys
        $this->campoOculto('ref_cod_matricula', $this->ref_cod_matricula);
        $this->campoOculto('ref_cod_serie', $this->ref_cod_serie);
        $this->campoOculto('ref_cod_escola', $this->ref_cod_escola);

        $opcoes = ['' => 'Selecione'];

        // Seleciona os componentes curriculares da turma

        try {
            $componentes = App_Model_IedFinder::getComponentesTurma(
                $this->ref_cod_serie,
                $this->ref_cod_escola,
                $this->ref_cod_turma
            );
        } catch (App_Model_Exception $e) {
            $this->mensagem = $e->getMessage();

            return;
        }

        foreach ($componentes as $componente) {
            $opcoes[$componente->id] = $componente->nome;
        }

        if ($this->ref_cod_disciplina) {
            $this->campoRotulo('nm_disciplina', 'Disciplina', $opcoes[$this->ref_cod_disciplina]);
            $this->campoOculto('ref_cod_disciplina', $this->ref_cod_disciplina);
        } else {
            $this->campoLista(
                'ref_cod_disciplina',
                'Disciplina',
                $opcoes,
                $this->ref_cod_disciplina
            );
        }

        $this->campoMemo('observacao', 'Observa&ccedil;&atilde;o', $this->observacao, 60, 10, false);
    }

    public function existeComponenteSerie()
    {
        $db = new clsBanco();
        $sql = "SELECT  EXISTS (SELECT 1
                                FROM pmieducar.escola_serie_disciplina
                               WHERE ref_ref_cod_serie = {$this->ref_cod_serie}
                                 AND ref_ref_cod_escola = {$this->ref_cod_escola}
                                 AND ref_cod_disciplina = {$this->ref_cod_disciplina}
                                 AND escola_serie_disciplina.ativo = 1)";

        return dbBool($db->campoUnico($sql));
    }

    public function validaQuantidadeDisciplinasDependencia()
    {
        $db = new clsBanco();
        $db->consulta("SELECT (CASE
                              WHEN escola.utiliza_regra_diferenciada THEN regra_avaliacao_diferenciada.qtd_disciplinas_dependencia
                              ELSE regra_avaliacao.qtd_disciplinas_dependencia
                          END) AS qtd_disciplinas_dependencia
                  FROM pmieducar.escola,
                       pmieducar.serie
                  LEFT JOIN modules.regra_avaliacao ON (serie.regra_avaliacao_id = regra_avaliacao.id)
                  LEFT JOIN modules.regra_avaliacao AS regra_avaliacao_diferenciada ON (serie.regra_avaliacao_diferenciada_id = regra_avaliacao_diferenciada.id)
                  WHERE serie.cod_serie = {$this->ref_cod_serie}
                    AND escola.cod_escola = {$this->ref_cod_escola}");

        $db->ProximoRegistro();
        $m = $db->Tupla();
        $qtdDisciplinasLimite = $m['qtd_disciplinas_dependencia'];

        $db->consulta("SELECT COUNT(1) as qtd
                    FROM pmieducar.disciplina_dependencia
                    WHERE ref_cod_matricula = {$this->ref_cod_matricula} ");
        $db->ProximoRegistro();
        $m = $db->Tupla();
        $qtdDisciplinas = $m['qtd'];

        $valid = $qtdDisciplinas < $qtdDisciplinasLimite;

        if (!$valid) {
            $this->mensagem .= "A regra desta s&eacute;rie limita a quantidade de disciplinas de depend&ecirc;ncia para {$qtdDisciplinasLimite}. <br/>";
        }

        return $valid;
    }

    public function Novo()
    {
        @session_start();
        $this->pessoa_logada = $_SESSION['id_pessoa'];
        @session_write_close();

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(
            578,
            $this->pessoa_logada,
            7,
            'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula
        );

        if (!$this->validaQuantidadeDisciplinasDependencia()) {
            return false;
        }

        if (!$this->existeComponenteSerie()) {
            $this->mensagem = 'O componente não está habilitado na série da escola.';
            $this->url_cancelar = 'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula;
            $this->nome_url_cancelar = 'Cancelar';

            return false;
        }

        $sql = 'SELECT MAX(cod_disciplina_dependencia) + 1 FROM pmieducar.disciplina_dependencia';
        $db = new clsBanco();
        $max_cod_disciplina_dependencia = $db->CampoUnico($sql);

        // Caso nÃ£o exista nenhuma dispensa, atribui o cÃ³digo 1, tabela nÃ£o utiliza sequences
        $max_cod_disciplina_dependencia = $max_cod_disciplina_dependencia > 0 ? $max_cod_disciplina_dependencia : 1;

        $obj = new clsPmieducarDisciplinaDependencia(
            $this->ref_cod_matricula,
            $this->ref_cod_serie,
            $this->ref_cod_escola,
            $this->ref_cod_disciplina,
            $this->observacao,
            $max_cod_disciplina_dependencia
        );

        if ($obj->existe()) {
            $obj = new clsPmieducarDisciplinaDependencia(
                $this->ref_cod_matricula,
                $this->ref_cod_serie,
                $this->ref_cod_escola,
                $this->ref_cod_disciplina,
                $this->observacao
            );

            $obj->edita();
            header('Location: educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula);
            die();
        }

        $cadastrou = $obj->cadastra();
        if ($cadastrou) {
            $this->mensagem .= 'Cadastro efetuado com sucesso.<br />';
            header('Location: educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula);
            die();
        }

        $this->mensagem = 'Cadastro n&atilde;o realizado.<br />';
        echo "<!--\nErro ao cadastrar clsPmieducarDisciplinaDependencia\nvalores obrigatorios\n is_numeric( $this->ref_cod_matricula ) && is_numeric( $this->ref_cod_serie ) && is_numeric( $this->ref_cod_escola ) && is_numeric( $this->ref_cod_disciplina ) \n-->";

        return false;
    }

    public function Editar()
    {
        @session_start();
        $this->pessoa_logada = $_SESSION['id_pessoa'];
        @session_write_close();

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_cadastra(
            578,
            $this->pessoa_logada,
            7,
            'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula
        );

        $obj = new clsPmieducarDisciplinaDependencia(
            $this->ref_cod_matricula,
            $this->ref_cod_serie,
            $this->ref_cod_escola,
            $this->ref_cod_disciplina,
            $this->observacao
        );

        $editou = $obj->edita();
        if ($editou) {
            $this->mensagem .= 'Edi&ccedil;&atilde;o efetuada com sucesso.<br />';
            header('Location: educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula);
            die();
        }

        $this->mensagem = 'Edi&ccedil;&atilde;o nÃ£o realizada.<br />';
        echo "<!--\nErro ao editar clsPmieducarDisciplinaDependencia\nvalores obrigatorios\nif( is_numeric( $this->ref_cod_matricula ) && is_numeric( $this->ref_cod_serie ) && is_numeric( $this->ref_cod_escola ) && is_numeric( $this->ref_cod_disciplina )  )\n-->";

        return false;
    }

    public function Excluir()
    {
        @session_start();
        $this->pessoa_logada = $_SESSION['id_pessoa'];
        @session_write_close();

        $obj_permissoes = new clsPermissoes();
        $obj_permissoes->permissao_excluir(
            578,
            $this->pessoa_logada,
            7,
            'educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula
        );

        $obj = new clsPmieducarDisciplinaDependencia(
            $this->ref_cod_matricula,
            $this->ref_cod_serie,
            $this->ref_cod_escola,
            $this->ref_cod_disciplina,
            $this->observacao
        );

        $excluiu = $obj->excluir();

        if ($excluiu) {
            $this->mensagem .= 'Exclus&atilde;o efetuada com sucesso.<br />';
            header('Location: educar_disciplina_dependencia_lst.php?ref_cod_matricula=' . $this->ref_cod_matricula);
            die();
        }

        $this->mensagem = 'Exclus&atilde;o nÃ£o realizada.<br />';
        echo "<!--\nErro ao excluir clsPmieducarDisciplinaDependencia\nvalores obrigatorios\nif( is_numeric( $this->ref_cod_matricula ) && is_numeric( $this->ref_cod_serie ) && is_numeric( $this->ref_cod_escola ) && is_numeric( $this->ref_cod_disciplina ) && is_numeric( $this->pessoa_logada ) )\n-->";

        return false;
    }
}

// Instancia objeto de pÃ¡gina
$pagina = new clsIndexBase();

// Instancia objeto de conteÃºdo
$miolo = new indice();

// Atribui o conteÃºdo Ã   pÃ¡gina
$pagina->addForm($miolo);

// Gera o cÃ³digo HTML
$pagina->MakeAll();
