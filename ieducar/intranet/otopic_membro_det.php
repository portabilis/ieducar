<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    *                                                                        *
    *   @author Prefeitura Municipal de Itajaí                               *
    *   @updated 29/03/2007                                                  *
    *   Pacote: i-PLB Software Público Livre e Brasileiro                    *
    *                                                                        *
    *   Copyright (C) 2006  PMI - Prefeitura Municipal de Itajaí             *
    *                       ctima@itajai.sc.gov.br                           *
    *                                                                        *
    *   Este  programa  é  software livre, você pode redistribuí-lo e/ou     *
    *   modificá-lo sob os termos da Licença Pública Geral GNU, conforme     *
    *   publicada pela Free  Software  Foundation,  tanto  a versão 2 da     *
    *   Licença   como  (a  seu  critério)  qualquer  versão  mais  nova.    *
    *                                                                        *
    *   Este programa  é distribuído na expectativa de ser útil, mas SEM     *
    *   QUALQUER GARANTIA. Sem mesmo a garantia implícita de COMERCIALI-     *
    *   ZAÇÃO  ou  de ADEQUAÇÃO A QUALQUER PROPÓSITO EM PARTICULAR. Con-     *
    *   sulte  a  Licença  Pública  Geral  GNU para obter mais detalhes.     *
    *                                                                        *
    *   Você  deve  ter  recebido uma cópia da Licença Pública Geral GNU     *
    *   junto  com  este  programa. Se não, escreva para a Free Software     *
    *   Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA     *
    *   02111-1307, USA.                                                     *
    *                                                                        *
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
$desvio_diretorio = '';
require_once('include/clsBase.inc.php');
require_once('include/clsDetalhe.inc.php');
require_once('include/clsListagem.inc.php');
require_once('include/otopic/otopicGeral.inc.php');
require_once('include/clsBanco.inc.php');
require_once('include/relatorio.inc.php');

class clsIndex extends clsBase
{
    public function Formular()
    {
        $this->SetTitulo("{$this->_instituicao} i-Pauta - Detalhe de Membro");
        $this->processoAp = '294';
    }
}

class indice extends clsDetalhe
{
    public function Gerar()
    {
        @session_start();
        $id_visualiza = $_SESSION['id_pessoa'];
        @session_write_close();

        $this->titulo = 'Detalhe do Membro';
        $this->addBanner('imagens/nvp_top_intranet.jpg', 'imagens/nvp_vert_intranet.jpg', 'Intranet', false);

        //pdf do relatório
        $objRelatorio = new relatorios('Relatório', 80, false, false, 'A4', "Prefeitura de Itajaí\nCentro Tecnologico de Informação e Modernização Administrativa.\nRua Alberto Werner, 100 - Vila Operária\nCEP. 88304-053 - Itajaí - SC", '#FFFFFF', '#000000', '#FFFFFF', '#FFFFFF');
        $objRelatorio_cor = new relatorios('Relatório', 80, false, false, 'A4', "Prefeitura de Itajaí\nCentro Tecnologico de Informação e Modernização Administrativa.\nRua Alberto Werner, 100 - Vila Operária\nCEP. 88304-053 - Itajaí - SC");

        $cod_membro = $_GET['cod_membro'];
        $cod_grupo = $_GET['cod_grupo'];

        $obj_moderador = new clsGrupoModerador($cod_membro, $cod_grupo);
        $detalhe_moderador = $obj_moderador->detalhe();
        $obj_grupo_pessoa = new clsGrupoPessoa($cod_membro, $cod_grupo);
        $detalhe_grupo_pessoa = $obj_grupo_pessoa->detalhe();

        if ($detalhe_moderador|| $detalhe_grupo_pessoa) {
            $obj_pessoa = new clsPessoaFisica($cod_membro);
            $detalhe = $obj_pessoa->queryRapida(
                $cod_membro,
                                                    'nome',
                                                    'cpf',
                                                    'sexo',
                                                    'email',
                                                    'ddd_1',
                                                    'fone_1',
                                                    'ddd_2',
                                                    'fone_2',
                                                    'ddd_mov',
                                                    'fone_mov',
                                                    'idtlog',
                                                    'logradouro',
                                                    'idlog',
                                                    'cidade',
                                                    'bairro',
                                                    'idbai',
                                                    'sigla_uf',
                                                    'cep',
                                                    'numero',
                                                    'complemento',
                                                    'andar'
            );

            $this->addDetalhe(['<b><i> Dados Pessoais</i></b>']);
            //pdf
            $objRelatorio->novalinha(['Dados Pessoais'], 0, 16, true, 'arial', false, '#FFFFFF', false, '#000000');
            $objRelatorio_cor->novalinha(['Dados Pessoais'], 0, 13, true);

            $this->addDetalhe(['Nome', $detalhe['nome']]);
            //pdf
            $objRelatorio->novalinha(['Nome:', $detalhe['nome']], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['Nome:', $detalhe['nome']], 15);

            if ($detalhe['cpf']) {
                $this->addDetalhe(['CPF', int2CPF($detalhe['cpf']) ]);
                $objRelatorio->novalinha(['CPF:', $detalhe['cpf']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['CPF:', $detalhe['cpf']], 15);
            }

            $sexo = ($detalhe['sexo'] == 'M') ? 'Masculino' : 'Feminino';
            $this->addDetalhe(['Sexo', $sexo]);
            //pdf
            $objRelatorio->novalinha(['Sexo:', $sexo], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['Sexo:', $sexo], 15);

            $this->addDetalhe(['E-mail', $detalhe['email']]);
            //pdf
            $objRelatorio->novalinha(['E-mail:', $detalhe['email']], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['E-mail:', $detalhe['email']], 15);

            if ($detalhe['ddd_1'] && $detalhe['fone_1']) {
                $this->addDetalhe(['Telefone', "({$detalhe['ddd_1']}) {$detalhe['fone_1']}"]);
                //pdf
                $objRelatorio->novalinha(['Telefone', "({$detalhe['ddd_1']}) {$detalhe['fone_1']}"], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Telefone', "({$detalhe['ddd_1']}) {$detalhe['fone_1']}"], 15);
            }

            if ($detalhe['ddd_2'] && $detalhe['fone_2']) {
                $this->addDetalhe(['Telefone 2', "({$detalhe['ddd_2']}) {$detalhe['fone_2']}"]);
                //pdf
                $objRelatorio->novalinha(['Telefone 2', "({$detalhe['ddd_2']}) {$detalhe['fone_2']}"], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Telefone 2', "({$detalhe['ddd_2']}) {$detalhe['fone_2']}"], 15);
            }

            if ($detalhe['ddd_mov'] && $detalhe['fone_mov']) {
                $this->addDetalhe(['Celular', "({$detalhe['ddd_mov']}) {$detalhe['fone_mov']}"]);
                //pdf
                $objRelatorio->novalinha(['Celular', "({$detalhe['ddd_mov']}) {$detalhe['fone_mov']}"], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Celular', "({$detalhe['ddd_mov']}) {$detalhe['fone_mov']}"], 15);
            }

            if ($detalhe['idlog']) {
                $obj_logradouro = new clsPublicLogradouro($detalhe['idlog']);
                $det_logradouro = $obj_logradouro->detalhe();
                $logradouro = $det_logradouro['nome'];
            } else {
                $logradouro = $detalhe['logradouro'];
            }

            $this->addDetalhe(['Endereço', "{$detalhe['idtlog']} {$logradouro}"]);
            //pdf
            $objRelatorio->novalinha(['Endereço', "({$detalhe['idtlog']}) {$logradouro}"], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['Endereço', "({$detalhe['idtlog']}) {$logradouro}"], 15);

            if ($detalhe['cidade']) {
                $this->addDetalhe(['Cidade', "{$detalhe['cidade']}"]);
                //pdf
                $objRelatorio->novalinha(['Cidade', $detalhe['cidade']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Cidade', $detalhe['cidade']], 15);
            }

            if ($detalhe['idbai']) {
                $obj_bairro = new clsPublicBairro(null, null, $detalhe['idbai']);
                $det_bairro = $obj_bairro->detalhe();
                $bairro = $det_bairro['nome'];
            } else {
                $bairro = $detalhe['bairro'];
            }

            $this->addDetalhe(['Bairro', $bairro]);
            //pdf
            $objRelatorio->novalinha(['Bairro', $bairro], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['Bairro', $bairro], 15);

            if ($detalhe['sigla_uf']) {
                $obj_Uf = new clsPublicUf($detalhe['sigla_uf']);
                $det_Uf = $obj_Uf->detalhe();

                $this->addDetalhe(['UF', $det_Uf['nome']]);
                //pdf
                $objRelatorio->novalinha(['UF', $det_Uf['nome']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['UF', $det_Uf['nome']], 15);
            }

            $detalhe['cep'] = int2CEP($detalhe['cep']);
            $this->addDetalhe(['CEP', $detalhe['cep']]);
            //pdf
            $objRelatorio->novalinha(['CEP', $detalhe['cep']], 15, 13, false, false, 60, false, '#FFFFFF');
            $objRelatorio_cor->novalinha(['CEP', $detalhe['cep']], 15);

            if ($detalhe['numero']) {
                $this->addDetalhe(['Número', $detalhe['numero']]);
                //pdf
                $objRelatorio->novalinha(['Número', $detalhe['numero']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Número', $detalhe['numero']], 15);
            }

            if ($detalhe['complemento']) {
                $this->addDetalhe(['Complemento', $detalhe['complemento']]);
                //pdf
                $objRelatorio->novalinha(['Complemento', $detalhe['complemento']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Complemento', $detalhe['complemento']], 15);
            }

            if ($detalhe['andar']) {
                $this->addDetalhe(['Andar', $detalhe['andar']]);
                //pdf
                $objRelatorio->novalinha(['Andar', $detalhe['andar']], 15, 13, false, false, 60, false, '#FFFFFF');
                $objRelatorio_cor->novalinha(['Andar', $detalhe['andar']], 15);
            }
        } else {
            header('Location: otopic_meus_grupos_lst.php');
        }

        $obj_moderador = new clsGrupoModerador($id_visualiza, $cod_grupo);
        $detalhe_moderador = $obj_moderador->detalhe();
        if ($id_visualiza != $cod_membro && $detalhe_moderador && $detalhe_moderador['ativo']==1) {
            $this->url_editar = "otopic_membros_cad.php?cod_grupo=$cod_grupo&cod_pessoa_fj=$cod_membro";
        }
        $this->url_cancelar = "otopic_meus_grupos_det.php?cod_grupo=$cod_grupo";

        $this->largura = '100%';

        //pdf - Notas
        $cod_membro = $_GET['cod_membro'];
        $cod_grupo = $_GET['cod_grupo'];
        if ($id_visualiza != $cod_membro) {
            $obj = new clsNotas();
            $lista = $obj->lista($cod_membro);
            if ($lista) {
                $objRelatorio->novalinha(['Notas'], 0, 16, true, 'arial', false, '#FFFFFF', false, '#000000');
                $objRelatorio_cor->novalinha(['Notas'], 0, 13, true);
                foreach ($lista as $notas) {
                    $total = $notas['total'];
                    //pdf
                    $objRelatorio->novalinha(["{$notas['nota']}"], 15, 13, false, false, 40, false, '#FFFFFF');
                    $objRelatorio_cor->novalinha(["{$notas['nota']}"], 15);
                }
            }
        }
        //fecha o pdf
        $link = $objRelatorio->fechaPdf();
        $link_cor = $objRelatorio_cor->fechaPdf();
        $this->array_botao = ['Imprimir (Jato)', 'Imprimir (Laser)'];
        $this->array_botao_url = ["$link", $link_cor];
    }
}

class Listas extends clsListagem
{
    public function Gerar()
    {
        @session_start();
        $id_visualiza = $_SESSION['id_pessoa'];
        @session_write_close();

        $this->titulo = 'Notas';
        $this->addBanner();

        $cod_membro = $_GET['cod_membro'];
        $cod_grupo = $_GET['cod_grupo'];

        $this->addCabecalhos([ 'Notas' ]);

        // Paginador
        $limite = 10;
        $iniciolimit = ($_GET["pagina_{$this->nome}"]) ? $_GET["pagina_{$this->nome}"]*$limite-$limite: 0;

        if ($id_visualiza != $cod_membro) {
            $obj = new clsNotas();
            $lista = $obj->lista($cod_membro);
            if ($lista) {
                foreach ($lista as $notas) {
                    $total =$notas['total'];
                    $this->addLinhas(["<a href='otopic_notas_cad.php?cod_membro=$cod_membro&cod_grupo=$cod_grupo&sequencial={$notas['sequencial']}'>{$notas['nota']}</a>"]);
                }
            }

            $this->acao = "go(\"otopic_notas_cad.php?cod_membro=$cod_membro&cod_grupo=$cod_grupo\")";
            $this->nome_acao = 'Novo';
        }

        $this->largura = '100%';
        $this->addPaginador2("otopic_membro_det.php?cod_membro=$cod_membro&cod_grupo=$cod_grupo", $total, $_GET, $this->nome, $limite);
    }
}

$pagina = new clsIndex();

$miolo = new indice();
$pagina->addForm($miolo);
$miolo = new Listas();
$pagina->addForm($miolo);

$pagina->MakeAll();
