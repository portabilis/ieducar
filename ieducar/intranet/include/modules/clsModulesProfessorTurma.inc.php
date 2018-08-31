<?php

require_once 'include/pmieducar/geral.inc.php';

class clsModulesProfessorTurma
{
    public $id;
    public $ano;
    public $instituicao_id;
    public $servidor_id;
    public $turma_id;
    public $funcao_exercida;
    public $tipo_vinculo;
    public $permite_lancar_faltas_componente;
    public $codUsuario;
    public $pessoa_logada;
    /**
     * Armazena o total de resultados obtidos na última chamada ao método lista().
     *
     * @var int
     */
    public $_total;

    /**
     * Nome do schema.
     *
     * @var string
     */
    public $_schema;

    /**
     * Nome da tabela.
     *
     * @var string
     */
    public $_tabela;

    /**
     * Lista separada por vírgula, com os campos que devem ser selecionados na
     * próxima chamado ao método lista().
     *
     * @var string
     */
    public $_campos_lista;

    /**
     * Lista com todos os campos da tabela separados por vírgula, padrão para
     * seleção no método lista.
     *
     * @var string
     */
    public $_todos_campos;

    /**
     * Valor que define a quantidade de registros a ser retornada pelo método lista().
     *
     * @var int
     */
    public $_limite_quantidade;

    /**
     * Define o valor de offset no retorno dos registros no método lista().
     *
     * @var int
     */
    public $_limite_offset;

    /**
     * Define o campo para ser usado como padrão de ordenação no método lista().
     *
     * @var string
     */
    public $_campo_order_by;

    /**
     * Construtor.
     */
    public function __construct($id = null, $ano = null, $instituicao_id = null, $servidor_id = null, $turma_id = null, $funcao_exercida = null, $tipo_vinculo = null, $permite_lancar_faltas_componente = null)
    {
        $db = new clsBanco();
        $this->_schema = 'modules.';
        $this->_tabela = "{$this->_schema}professor_turma";
        $this->pessoa_logada = $_SESSION['id_pessoa'];

        $this->_campos_lista = $this->_todos_campos = ' pt.id, pt.ano, pt.instituicao_id, pt.servidor_id, pt.turma_id, pt.funcao_exercida, pt.tipo_vinculo, pt.permite_lancar_faltas_componente';

        if (is_numeric($id)) {
            $this->id = $id;
        }

        if (is_numeric($turma_id)) {
            $this->turma_id = $turma_id;
        }

        if (is_numeric($ano)) {
            $this->ano = $ano;
        }

        if (is_numeric($instituicao_id)) {
            $this->instituicao_id = $instituicao_id;
        }

        if (is_numeric($servidor_id)) {
            $this->servidor_id = $servidor_id;
        }

        if (is_numeric($funcao_exercida)) {
            $this->funcao_exercida = $funcao_exercida;
        }

        if (is_numeric($tipo_vinculo)) {
            $this->tipo_vinculo = $tipo_vinculo;
        }

        if (isset($permite_lancar_faltas_componente)) {
            $this->permite_lancar_faltas_componente = '1';
        } else {
            $this->permite_lancar_faltas_componente = '0';
        }
    }

    /**
     * Cria um novo registro.
     *
     * @return bool
     */
    public function cadastra()
    {
        if (is_numeric($this->turma_id) && is_numeric($this->funcao_exercida) && is_numeric($this->ano)
          && is_numeric($this->servidor_id) && is_numeric($this->instituicao_id)) {
            $db = new clsBanco();
            $campos  = '';
            $valores = '';
            $gruda   = '';

            if (is_numeric($this->instituicao_id)) {
                $campos .= "{$gruda}instituicao_id";
                $valores .= "{$gruda}'{$this->instituicao_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->ano)) {
                $campos .= "{$gruda}ano";
                $valores .= "{$gruda}'{$this->ano}'";
                $gruda = ', ';
            }

            if (is_numeric($this->servidor_id)) {
                $campos .= "{$gruda}servidor_id";
                $valores .= "{$gruda}'{$this->servidor_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->turma_id)) {
                $campos .= "{$gruda}turma_id";
                $valores .= "{$gruda}'{$this->turma_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->funcao_exercida)) {
                $campos .= "{$gruda}funcao_exercida";
                $valores .= "{$gruda}'{$this->funcao_exercida}'";
                $gruda = ', ';
            }

            if (is_numeric($this->tipo_vinculo)) {
                $campos .= "{$gruda}tipo_vinculo";
                $valores .= "{$gruda}'{$this->tipo_vinculo}'";
                $gruda = ', ';
            }

            if (is_numeric($this->permite_lancar_faltas_componente)) {
                $campos .= "{$gruda}permite_lancar_faltas_componente";
                $valores .= "{$gruda}'{$this->permite_lancar_faltas_componente}'";
                $gruda = ', ';
            }

            $campos .= "{$gruda}updated_at";
            $valores .= "{$gruda} CURRENT_TIMESTAMP";
            $gruda = ', ';

            $db->Consulta("INSERT INTO {$this->_tabela} ( $campos ) VALUES( $valores )");

            $id = $db->InsertId("{$this->_tabela}_id_seq");
            $this->id = $id;
            $auditoria = new clsModulesAuditoriaGeral('professor_turma', $this->pessoa_logada, $id);
            $auditoria->inclusao($this->detalhe());

            return $id;
        }

        return false;
    }

    /**
     * Edita os dados de um registro.
     *
     * @return bool
     */
    public function edita()
    {
        if (is_numeric($this->id) && is_numeric($this->turma_id) && is_numeric($this->funcao_exercida) && is_numeric($this->ano)
          && is_numeric($this->servidor_id) && is_numeric($this->instituicao_id)) {
            $db  = new clsBanco();
            $set = '';
            $gruda = '';

            if (is_numeric($this->ano)) {
                $set .= "{$gruda}ano = '{$this->ano}'";
                $gruda = ', ';
            }

            if (is_numeric($this->instituicao_id)) {
                $set .= "{$gruda}instituicao_id = '{$this->instituicao_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->servidor_id)) {
                $set .= "{$gruda}servidor_id = '{$this->servidor_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->turma_id)) {
                $set .= "{$gruda}turma_id = '{$this->turma_id}'";
                $gruda = ', ';
            }

            if (is_numeric($this->funcao_exercida)) {
                $set .= "{$gruda}funcao_exercida = '{$this->funcao_exercida}'";
                $gruda = ', ';
            }

            if (is_numeric($this->tipo_vinculo)) {
                $set .= "{$gruda}tipo_vinculo = '{$this->tipo_vinculo}'";
                $gruda = ', ';
            } elseif (is_null($this->tipo_vinculo)) {
                $set .= "{$gruda}tipo_vinculo = NULL";
                $gruda = ', ';
            }

            if (is_numeric($this->permite_lancar_faltas_componente)) {
                $set .= "{$gruda}permite_lancar_faltas_componente = '{$this->permite_lancar_faltas_componente}'";
                $gruda = ', ';
            }

            $set .= "{$gruda}updated_at = CURRENT_TIMESTAMP";
            $gruda = ', ';

            if ($set) {
                $detalheAntigo = $this->detalhe();
                $db->Consulta("UPDATE {$this->_tabela} SET $set WHERE id = '{$this->id}'");
                $detalheAtual = $this->detalhe();
                $auditoria = new clsModulesAuditoriaGeral('professor_turma', $this->pessoa_logada, $this->id);
                $auditoria->alteracao($detalheAntigo, $detalheAtual);

                return true;
            }
        }

        return false;
    }

    /**
     * Retorna uma lista de registros filtrados de acordo com os parâmetros.
     *
     * @return array
     */
    public function lista(
        $servidor_id = null,
        $instituicao_id = null,
        $ano = null,
        $ref_cod_escola = null,
        $ref_cod_curso = null,
        $ref_cod_serie = null,
        $ref_cod_turma = null,
        $funcao_exercida = null,
        $tipo_vinculo = null
    ) {
        $sql = "SELECT {$this->_campos_lista}, t.nm_turma, t.cod_turma as ref_cod_turma, t.ref_ref_cod_serie as ref_cod_serie,
            s.nm_serie, t.ref_cod_curso, c.nm_curso, t.ref_ref_cod_escola as ref_cod_escola, p.nome as nm_escola
            FROM {$this->_tabela} pt";
        $filtros = ' , pmieducar.turma t, pmieducar.serie s, pmieducar.curso c, pmieducar.escola e, cadastro.pessoa p WHERE pt.turma_id = t.cod_turma AND t.ref_ref_cod_serie = s.cod_serie AND s.ref_cod_curso = c.cod_curso
                  AND t.ref_ref_cod_escola = e.cod_escola AND e.ref_idpes = p.idpes ';

        $whereAnd = ' AND ';

        if (is_numeric($servidor_id)) {
            $filtros .= "{$whereAnd} pt.servidor_id = '{$servidor_id}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($instituicao_id)) {
            $filtros .= "{$whereAnd} pt.instituicao_id = '{$instituicao_id}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($ano)) {
            $filtros .= "{$whereAnd} pt.ano = '{$ano}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($ref_cod_escola)) {
            $filtros .= "{$whereAnd} t.ref_ref_cod_escola = '{$ref_cod_escola}'";
            $whereAnd = ' AND ';
        } elseif ($this->codUsuario) {
            $filtros .= "{$whereAnd} EXISTS (SELECT 1
                                         FROM pmieducar.escola_usuario
                                        WHERE escola_usuario.ref_cod_escola = t.ref_ref_cod_escola
                                          AND escola_usuario.ref_cod_usuario = '{$this->codUsuario}')";
            $whereAnd = ' AND ';
        }

        if (is_numeric($ref_cod_curso)) {
            $filtros .= "{$whereAnd} t.ref_cod_curso = '{$ref_cod_curso}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($ref_cod_serie)) {
            $filtros .= "{$whereAnd} t.ref_ref_cod_serie = '{$ref_cod_serie}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($ref_cod_turma)) {
            $filtros .= "{$whereAnd} t.cod_turma = '{$ref_cod_turma}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($funcao_exercida)) {
            $filtros .= "{$whereAnd} pt.funcao_exercida = '{$funcao_exercida}'";
            $whereAnd = ' AND ';
        }

        if (is_numeric($tipo_vinculo)) {
            $filtros .= "{$whereAnd} pt.tipo_vinculo = '{$tipo_vinculo}'";
            $whereAnd = ' AND ';
        }

        $db = new clsBanco();
        $countCampos = count(explode(',', $this->_campos_lista))+8;
        $resultado = [];

        $sql .= $filtros . $this->getOrderby() . $this->getLimite();

        $this->_total = $db->CampoUnico("SELECT COUNT(0) FROM {$this->_tabela} pt {$filtros}");

        $db->Consulta($sql);

        if ($countCampos > 1) {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $tupla['_total'] = $this->_total;
                $resultado[] = $tupla;
            }
        } else {
            while ($db->ProximoRegistro()) {
                $tupla = $db->Tupla();
                $resultado[] = $tupla[$this->_campos_lista];
            }
        }
        if (count($resultado)) {
            return $resultado;
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro.
     *
     * @return array
     */
    public function detalhe()
    {
        if (is_numeric($this->id)) {
            $db = new clsBanco();
            $db->Consulta("SELECT {$this->_campos_lista}, t.nm_turma, s.nm_serie, c.nm_curso, p.nome as nm_escola
                     FROM {$this->_tabela} pt, pmieducar.turma t, pmieducar.serie s, pmieducar.curso c,
                     pmieducar.escola e, cadastro.pessoa p
                     WHERE pt.turma_id = t.cod_turma AND t.ref_ref_cod_serie = s.cod_serie AND s.ref_cod_curso = c.cod_curso
                     AND t.ref_ref_cod_escola = e.cod_escola AND e.ref_idpes = p.idpes AND id = '{$this->id}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    /**
     * Retorna um array com os dados de um registro.
     *
     * @return array
     */
    public function existe()
    {
        if (is_numeric($this->id)) {
            $db = new clsBanco();
            $db->Consulta("SELECT 1 FROM {$this->_tabela} pt WHERE id = '{$this->id}'");
            $db->ProximoRegistro();

            return $db->Tupla();
        }

        return false;
    }

    public function existe2()
    {
        if (is_numeric($this->ano) && is_numeric($this->instituicao_id) && is_numeric($this->servidor_id)
        && is_numeric($this->turma_id)) {
            $db = new clsBanco();
            $sql = "SELECT id FROM {$this->_tabela} pt WHERE ano = '{$this->ano}' AND turma_id = '{$this->turma_id}'
               AND instituicao_id = '{$this->instituicao_id}' AND servidor_id = '{$this->servidor_id}' ";

            if (is_numeric($this->id)) {
                $sql .= " AND id <> {$this->id}";
            }

            return $db->UnicoCampo($sql);
        }

        return false;
    }

    /**
     * Exclui um registro.
     *
     * @return bool
     */
    public function excluir()
    {
        if (is_numeric($this->id)) {
            $detalhe = $this->detalhe();
            $sql = "DELETE FROM {$this->_tabela} pt WHERE id = '{$this->id}'";
            $db = new clsBanco();
            $db->Consulta($sql);
            $auditoria = new clsModulesAuditoriaGeral('professor_turma', $this->pessoa_logada, $this->id);
            $auditoria->exclusao($detalhe);

            return true;
        }

        return false;
    }

    public function gravaComponentes($professor_turma_id, $componentes)
    {
        $componentesAntigos = $this->retornaComponentesVinculados($professor_turma_id);
        $this->excluiComponentes($professor_turma_id);
        $db = new clsBanco();
        foreach ($componentes as $componente) {
            $db->Consulta("INSERT INTO modules.professor_turma_disciplina VALUES ({$professor_turma_id},{$componente})");
        }
        $componentesNovos = $this->retornaComponentesVinculados($professor_turma_id);
        $this->auditaComponentesVinculados($professor_turma_id, $componentesAntigos, $componentesNovos);
    }

    public function excluiComponentes($professor_turma_id)
    {
        $db = new clsBanco();
        $db->Consulta("DELETE FROM modules.professor_turma_disciplina WHERE professor_turma_id = {$professor_turma_id}");
    }

    public function retornaComponentesVinculados($professor_turma_id)
    {
        $componentesVinculados = [];
        $sql = "SELECT componente_curricular_id
                  FROM modules.professor_turma_disciplina
                 WHERE professor_turma_id = {$professor_turma_id}";
        $db = new clsBanco();
        $db->Consulta($sql);
        while ($db->ProximoRegistro()) {
            $tupla = $db->Tupla();
            $componentesVinculados[] = $tupla['componente_curricular_id'];
        }
        return $componentesVinculados;
    }

    private function auditaComponentesVinculados($professor_turma_id, $componentesAntigos, $componentesNovos)
    {
        $componentesExcluidos = array_diff($componentesAntigos, $componentesNovos);
        $componentesAdicionados = array_diff($componentesNovos, $componentesAntigos);

        $auditoria = new clsModulesAuditoriaGeral('professor_turma_disciplina', $this->pessoa_logada, $professor_turma_id);

        foreach ($componentesExcluidos as $componente) {
            $componente = [
                'componente_curricular_id' => $componente,
                'nome' => $this->retornaNomeDoComponente($componente)
            ];
            $auditoria->exclusao($componente);
        }

        foreach ($componentesAdicionados as $componente) {
            $componente = [
                'componente_curricular_id' => $componente,
                'nome' => $this->retornaNomeDoComponente($componente)
            ];
            $auditoria->inclusao($componente);
        }
    }

    public function retornaNomeDoComponente($idComponente)
    {
        $mapperComponente = new ComponenteCurricular_Model_ComponenteDataMapper;
        $componente = $mapperComponente->find(['id' => $idComponente]);
        return $componente->nome;
    }

    /**
     * Define quais campos da tabela serão selecionados no método Lista().
     */
    public function setCamposLista($str_campos)
    {
        $this->_campos_lista = $str_campos;
    }

    /**
     * Define que o método Lista() deverpa retornar todos os campos da tabela.
     */
    public function resetCamposLista()
    {
        $this->_campos_lista = $this->_todos_campos;
    }

    /**
     * Define limites de retorno para o método Lista().
     */
    public function setLimite($intLimiteQtd, $intLimiteOffset = null)
    {
        $this->_limite_quantidade = $intLimiteQtd;
        $this->_limite_offset = $intLimiteOffset;
    }

    /**
     * Retorna a string com o trecho da query responsável pelo limite de
     * registros retornados/afetados.
     *
     * @return string
     */
    public function getLimite()
    {
        if (is_numeric($this->_limite_quantidade)) {
            $retorno = " LIMIT {$this->_limite_quantidade}";
            if (is_numeric($this->_limite_offset)) {
                $retorno .= " OFFSET {$this->_limite_offset} ";
            }

            return $retorno;
        }

        return '';
    }

    /**
     * Define o campo para ser utilizado como ordenação no método Lista().
     */
    public function setOrderby($strNomeCampo)
    {
        if (is_string($strNomeCampo) && $strNomeCampo) {
            $this->_campo_order_by = $strNomeCampo;
        }
    }

    /**
     * Retorna a string com o trecho da query responsável pela Ordenação dos
     * registros.
     *
     * @return string
     */
    public function getOrderby()
    {
        if (is_string($this->_campo_order_by)) {
            return " ORDER BY {$this->_campo_order_by} ";
        }

        return '';
    }
}
