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
 * @author      Eriksen Costa Paixão <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     Avaliacao
 * @subpackage  UnitTests
 * @since       Arquivo disponível desde a versão 1.1.0
 * @version     $Id$
 */

require_once 'include/pmieducar/clsPmieducarMatricula.inc.php';
require_once 'include/pmieducar/clsPmieducarSerie.inc.php';
require_once 'include/pmieducar/clsPmieducarCurso.inc.php';
require_once 'include/pmieducar/clsPmieducarEscolaSerieDisciplina.inc.php';
require_once 'include/pmieducar/clsPmieducarDispensaDisciplina.inc.php';

require_once 'AreaConhecimento/Model/AreaDataMapper.php';

require_once 'FormulaMedia/Model/Formula.php';
require_once 'FormulaMedia/Model/FormulaDataMapper.php';

require_once 'TabelaArredondamento/Model/TabelaValor.php';
require_once 'TabelaArredondamento/Model/TabelaValorDataMapper.php';

require_once 'TabelaArredondamento/Model/Tabela.php';
require_once 'TabelaArredondamento/Model/TabelaDataMapper.php';

require_once 'RegraAvaliacao/Model/Regra.php';
require_once 'RegraAvaliacao/Model/RegraDataMapper.php';

require_once 'ComponenteCurricular/Model/TurmaDataMapper.php';

require_once 'Avaliacao/Model/NotaAlunoDataMapper.php';
require_once 'Avaliacao/Model/NotaComponenteDataMapper.php';
require_once 'Avaliacao/Model/NotaComponenteMediaDataMapper.php';

require_once 'Avaliacao/Model/FaltaAlunoDataMapper.php';
require_once 'Avaliacao/Model/FaltaGeralDataMapper.php';
require_once 'Avaliacao/Model/FaltaComponenteDataMapper.php';

require_once 'Avaliacao/Model/ParecerDescritivoAlunoDataMapper.php';
require_once 'Avaliacao/Model/ParecerDescritivoAbstractDataMapper.php';
require_once 'Avaliacao/Model/ParecerDescritivoComponenteDataMapper.php';
require_once 'Avaliacao/Model/ParecerDescritivoGeralDataMapper.php';

require_once 'Avaliacao/Service/Boletim.php';

/**
 * Avaliacao_Service_TestCommon abstract class.
 *
 * Configura o service Avaliacao_Service_Boletim com mocks de suas dependências
 * para que seja mais simples o processo de teste dos diversos comportamentos.
 * A configuração básica segue o melhor cenário: as instâncias de
 * Avaliacao_Model_NotaAluno e Avaliacao_Model_FaltaAluno existirão. Nenhuma
 * nota, média ou falta terá sido lançada.
 *
 * Alguns métodos podem ser sobrescritos para que os mocks retornem o
 * comportamento desejado para a sessão de testes (como os métodos
 * _getMatricula(), _getSerie()) ou, pode-se usar o setter _setConfigOption()
 * para alterar apenas um dos valores default da classe.
 *
 * A configuração padrão do service é constituída de:
 * - Código do usuário: 1
 * - Código de matrícula: 1
 * - RegraAvaliacao_Model_RegraDataMapper: configuração ampla. Ver o array
 *   $_regraOptions e o método _setRegraOption para entender a configuração
 *   da instância
 * - ComponenteCurricular_Model_ComponenteDataMapper: mock que retorna
 *   diferentes instâncias de ComponenteCurricular_Model_Componente em cada
 *   chamada. Essas instâncias são definidas na opção 'componenteCurricular'
 *   do array $_config. Esses componentes correspondem com os valores
 *   retornados pelos mocks de classes legadas (configuradas nos métodos
 *   _setUp*Mock()
 * - Avaliacao_Model_NotaAlunoDataMapper: mock que retorna uma instância
 *   de Avaliacao_Model_NotaAluno com as configurações padrão
 * - Avaliacao_Model_NotaComponenteDataMapper: mock que retorna um array
 *   vazio. Não existem notas lançadas para o aluno
 * - Avaliacao_Model_NotaComponenteMediaDataMapper: mock que retorna um array
 *   vazio. Não existem médias lançadas para o aluno
 * - Avaliacao_Model_FaltaAlunoDataMapper: mock que retorna uma instância
 *   de Avaliacao_Model_FaltaAluno com as configurações padrão
 * - Avaliacao_Model_FaltaAbstractDataMapper: mock que retorna um array
 *   vazio. Não existem faltas lançadas para o aluno.
 *   OBSERVAÇÃO: métodos que sobrescreverem este, devem estar conscientes de
 *   que é necessário configurar o mock para retornar objeto(s) de acordo
 *   com o 'tipoPresenca' da instância de 'RegraAvaliacao_Model_Regra'. Ver o
 *   array $_regraOptions para mais informações.
 *
 * Outro ponto fundamental é entender que boa parte da inicialização do service
 * constitui a chamadas de instâncias das classes legadas, encapsuladas nos
 * métodos de App_Model_IedFinder. Estes são configurados nos métodos
 * _setUp*Mock(). Para alterar o comportamento desses métodos, existem duas
 * opções:
 *
 * - Chamar o método _setConfigOptions() ou setConfigOption em uma sobrescrição
 * de setUp()
 * - Sobrescrever o método
 *
 * Recomenda-se usar os métodos _setConfigOption e _setRegraOption a
 * sobrescrever os métodos já que proporcionam mais possibilidade de
 * configuração para cada método de teste.
 *
 * @author      Eriksen Costa Paixão <eriksen.paixao_bs@cobra.com.br>
 * @category    i-Educar
 * @license     @@license@@
 * @package     Avaliacao
 * @subpackage  UnitTests
 * @since       Classe disponível desde a versão 1.1.0
 * @version     @@package_version@@
 */
abstract class Avaliacao_Service_TestCommon extends UnitBaseTest
{
  /**
   * Array com as diretrizes de configuração para uso nas dependências de
   * Avaliacao_Service_Boletim.
   * @var array
   */
  protected $_config = array();

  /**
   * @var RegraAvaliacao_Model_RegraDataMapper
   */
  protected $_regraDataMapperMock = NULL;

  /**
   * Opções de configuração para RegraAvaliacao_Model_RegraDataMapper. Por
   * padrão, a regra terá:
   *
   * - Identificador "1"
   * - Nome "Regra geral"
   * - Tipo de nota numérica
   * - Progressão continuada
   * - Presença por componente
   * - Nenhum parecer descritivo
   * - Média para promoção de "6"
   * - Tabela de arredondamento com valores de 0 a 10
   * - Fórmula de média aritmética simples (Soma etapas / Qtde etapas)
   * - Fórmula de recuperação ponderada (Soma etapas x 0.6 + Recuperação x 0.4)
   * - Porcentagem de presença mínima de "75%"
   *
   * Para alterar algum desses valores, basta usar o método
   * _setRegraOption($key, $value) onde $key é a chave do array e $value o valor
   * a ser usado.
   *
   * @var array
   */
  protected $_regraOptions = array(
    'id'                   => 1,
    'nome'                 => 'Regra geral',
    'tipoNota'             => RegraAvaliacao_Model_Nota_TipoValor::NUMERICA,
    'tipoProgressao'       => RegraAvaliacao_Model_TipoProgressao::CONTINUADA,
    'tipoPresenca'         => RegraAvaliacao_Model_TipoPresenca::POR_COMPONENTE,
    'parecerDescritivo'    => RegraAvaliacao_Model_TipoParecerDescritivo::NENHUM,
    'media'                => 6,
    'tabelaArredondamento' => NULL,
    'tabelaArredondamentoConceitual' => NULL,
    'formulaMedia'         => NULL,
    'formulaRecuperacao'   => NULL,
    'porcentagemPresenca'  => 75.0,
    'notaMaximaExameFinal' => 10,
    'mediaRecuperacao'     => 4.0
  );

  protected $_componenteCurricularMapperMock = NULL;

  protected $_componenteDataMapperMock = NULL;

  protected $_componenteTurmaDataMapperMock = NULL;

  protected $_notaAlunoDataMapperMock = NULL;

  protected $_notaComponenteDataMapperMock = NULL;

  protected $_notaComponenteMediaDataMapperMock = NULL;

  protected $_faltaAlunoDataMapperMock = NULL;

  protected $_faltaAbstractDataMapperMock = NULL;

  protected $_parecerDescritivoAlunoDataMapperMock = NULL;

  protected $_parecerDescritivoAbstractDataMapperMock = NULL;

  protected function setUp()
  {
    // Armazena valores de configuração para serem usados nas diferentes
    // instâncias de objetos legados e novos
    $this->_setConfigOptions('usuario', array('cod_usuario' => 1))
         ->_setConfigOptions('matricula', $this->_getMatricula())
         ->_setConfigOptions('matriculaTurma', $this->_getMatriculaTurma())
         ->_setConfigOptions('serie', $this->_getSerie())
         ->_setConfigOptions('curso', $this->_getCurso())
         ->_setConfigOptions('escolaAnoLetivo', $this->_getEscolaAnoLetivo())
         ->_setConfigOptions('anoLetivoModulo', $this->_getAnoLetivoModulo())
         ->_setConfigOptions('modulo', $this->_getModulo())
         ->_setConfigOptions('componentesTurma', $this->_getComponentesTurma())
         ->_setConfigOptions('escolaSerieDisciplina', $this->_getEscolaSerieDisciplina())
         ->_setConfigOptions('dispensaDisciplina', $this->_getDispensaDisciplina())
         ->_setConfigOptions('componenteCurricular', $this->_getComponenteCurricular())
         ->_setConfigOptions('notaAluno', $this->_getNotaAluno())
         ->_setConfigOptions('faltaAluno', $this->_getFaltaAluno())
         ->_setConfigOptions('parecerDescritivoAluno', $this->_getParecerDescritivoAluno());

    // Configura atributos de RegraAvaliacao_Model_Regra
    $this->_setRegraOption('formulaMedia', $this->_setUpFormulaMedia())
        ->_setRegraOption('formulaRecuperacao', $this->_setUpFormulaRecuperacao())
        ->_setRegraOption('tabelaArredondamento', $this->_setUpTabelaArredondamento())
        ->_setRegraOption('tabelaArredondamentoConceitual', $this->_setUpTabelaArredondamentoConceitual());
  }

  protected function _getServiceInstance()
  {
    // Configura mappers das dependências de Avalilacao_Service_Boletim
    $mappers = array(
      'RegraDataMapper'                     => $this->_getRegraDataMapperMock(),
      'ComponenteDataMapper'                => $this->_getComponenteDataMapperMock(),
      'ComponenteTurmaDataMapper'           => $this->_getComponenteTurmaDataMapperMock(),
      'NotaAlunoDataMapper'                 => $this->_getNotaAlunoDataMapperMock(),
      'NotaComponenteDataMapper'            => $this->_getNotaComponenteDataMapperMock(),
      'NotaComponenteMediaDataMapper'       => $this->_getNotaComponenteMediaDataMapperMock(),
      'FaltaAlunoDataMapper'                => $this->_getFaltaAlunoDataMapperMock(),
      'FaltaAbstractDataMapper'             => $this->_getFaltaAbstractDataMapperMock(),
      'ParecerDescritivoAlunoDataMapper'    => $this->_getParecerDescritivoAlunoDataMapperMock(),
      'ParecerDescritivoAbstractDataMapper' => $this->_getParecerDescritivoAbstractDataMapperMock(),
    );

    $this->_setConfigOptions('mappers', $mappers);

    // Cria os mocks das classes legadas
    $this->_setUpMatriculaMock()
         ->_setUpMatriculaTurmaMock()
         ->_setUpCursoMock()
         ->_setUpSerieMock()
         ->_setUpEscolaAnoLetivo()
         ->_setUpAnoLetivoModulo()
         ->_setUpModulo()
         ->_setUpEscolaSerieDisciplinaMock()
         ->_setUpDispensaDisciplinaMock();

    // Instancia o service
    return new Avaliacao_Service_Boletim($this->_getServiceOptions());
  }

  /**
   * Getter. Retorna o array de opções para a inicialização do service.
   * @return array
   */
  protected function _getServiceOptions()
  {
    return array(
      'matricula'                           => $this->_getConfigOption('matricula', 'cod_matricula'),
      'usuario'                             => $this->_getConfigOption('usuario', 'cod_usuario'),
      'RegraDataMapper'                     => $this->_getConfigOption('mappers', 'RegraDataMapper'),
      'ComponenteDataMapper'                => $this->_getConfigOption('mappers', 'ComponenteDataMapper'),
      'ComponenteTurmaDataMapper'           => $this->_getConfigOption('mappers', 'ComponenteTurmaDataMapper'),
      'NotaAlunoDataMapper'                 => $this->_getConfigOption('mappers', 'NotaAlunoDataMapper'),
      'NotaComponenteDataMapper'            => $this->_getConfigOption('mappers', 'NotaComponenteDataMapper'),
      'NotaComponenteMediaDataMapper'       => $this->_getConfigOption('mappers', 'NotaComponenteMediaDataMapper'),
      'FaltaAlunoDataMapper'                => $this->_getConfigOption('mappers', 'FaltaAlunoDataMapper'),
      'FaltaAbstractDataMapper'             => $this->_getConfigOption('mappers', 'FaltaAbstractDataMapper'),
      'ParecerDescritivoAlunoDataMapper'    => $this->_getConfigOption('mappers', 'ParecerDescritivoAlunoDataMapper'),
      'ParecerDescritivoAbstractDataMapper' => $this->_getConfigOption('mappers', 'ParecerDescritivoAbstractDataMapper'),
    );
  }

  /**
   * Setter.
   *
   * @param string $namespace
   * @param array $data
   * @return Avaliacao_Service_TestCommon
   */
  protected function _setConfigOptions($namespace, array $data)
  {
    $namespace = strtolower($namespace);

    $this->_config[$namespace] = array();

    // Chama _setConfigOption() para não sobrescrever opções já configuradas
    foreach ($data as $key => $value) {
      $this->_setConfigOption($namespace, $key, $value);
    }

    return $this;
  }

  /**
   * Getter.
   *
   * @param string $namespace
   * @return mixed
   */
  protected function _getConfigOptions($namespace)
  {
    $namespace = strtolower($namespace);

    if (!isset($this->_config[$namespace])) {
      throw new Exception('_getConfigOption namespace');
    }

    return $this->_config[$namespace];
  }

  /**
   * Setter.
   *
   * @param string $namespace
   * @param string $key
   * @param mixed $value
   * @return Avaliacao_Service_TestCommon
   */
  protected function _setConfigOption($namespace, $key, $value)
  {
    $namespace = strtolower($namespace);

    if (!isset($this->_config[$namespace])) {
      $this->_config[$namespace] = array();
    }

    $this->_config[$namespace][$key] = $value;
    return $this;
  }

  /**
   * Getter.
   *
   * @param string $namespace
   * @param string $key
   * @return mixed
   */
  protected function _getConfigOption($namespace, $key)
  {
    $namespace = strtolower($namespace);

    if (!isset($this->_config[$namespace])) {
      throw new Exception('_getConfigOption namespace');
    }

    if (!isset($this->_config[$namespace][$key])) {
      throw new Exception('_getConfigOption option name: ' . $key);
    }

    return $this->_config[$namespace][$key];
  }

  /**
   * @return array
   */
  protected function _getMatricula()
  {
    return array(
      'cod_matricula'      => 1,
      'ref_cod_curso'      => 1,
      'ref_ref_cod_serie'  => 1,
      'ref_ref_cod_escola' => 1,
      'aprovado'           => 1
    );
  }

  /**
   * @return array
   */
  protected function _getMatriculaTurma()
  {
    return array(
      'ref_cod_matricula' => 1,
      'ref_cod_turma'     => 1
    );
  }

  /**
   * @return array
   */
  protected function _getSerie()
  {
    return array(
      'regra_avaliacao_id' => 1,
      'carga_horaria'      => 800
    );
  }

  /**
   * @return array
   */
  protected function _getCurso()
  {
    return array(
      'carga_horaria'      => 800 * 9,
      'hora_falta'         => (50 / 60),
      'padrao_ano_escolar' => 1
    );
  }

  /**
   * @return array
   */
  protected function _getEscolaAnoLetivo()
  {
    return array(array(
      'ref_cod_escola' => 1,
      'ano' => 2009,
      'andamento' => 1,
      'ativo' => 1
    ));
  }

  /**
   * @return array
   */
  protected function _getAnoLetivoModulo()
  {
    return array(
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 1, 'ref_cod_modulo' => 1),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 2, 'ref_cod_modulo' => 1),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 3, 'ref_cod_modulo' => 1),
      array('ref_ano' => 2009, 'ref_ref_cod_escola' => 1, 'sequencial' => 4, 'ref_cod_modulo' => 1)
    );
  }

  /**
   * @return array
   */
  protected function _getModulo()
  {
    return array(
      'cod_modulo' => 1, 'nm_tipo' => 'Bimestre'
    );
  }

  /**
   * Retorna um array com as possíveis etapas a serem cursadas.
   * @return array
   * @todo Condicionar o retorno de 'Rc' caso exista recuperação na Regra
   */
  protected function _getEtapasPossiveis()
  {
    $etapas = count($this->_getConfigOptions('anoLetivoModulo'));
    return array_merge(range(1, $etapas, 1), array('Rc'));
  }

  /**
   * @return array
   */
  protected function _getComponentesTurma()
  {
    return array([]);
  }

  /**
   * @return array
   */
  protected function _getEscolaSerieDisciplina()
  {
    return array(
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 1, 'carga_horaria' => 250),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 2, 'carga_horaria' => 250),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 3, 'carga_horaria' => 150),
      array('ref_cod_serie' => 1, 'ref_cod_disciplina' => 4, 'carga_horaria' => 150),
    );
  }

  /**
   * @return array
   */
  protected function _getDispensaDisciplina()
  {
    return array([
        'ref_cod_disciplina' => 1
    ]);
  }

  /**
   * @return array
   */
  protected function _getComponenteCurricular()
  {
    return array(
      1 => new ComponenteCurricular_Model_Componente(
        array('id' => 1, 'nome' => 'Matemática', 'cargaHoraria' => 100)
      ),
      2 => new ComponenteCurricular_Model_Componente(
        array('id' => 2, 'nome' => 'Português', 'cargaHoraria' => 100)
      ),
      3 => new ComponenteCurricular_Model_Componente(
        array('id' => 3, 'nome' => 'Ciências', 'cargaHoraria' => 60)
      ),
      4 => new ComponenteCurricular_Model_Componente(
        array('id' => 4, 'nome' => 'Física', 'cargaHoraria' => 60)
      )
    );
  }

  /**
   * Retorna os componentes cursados pelo aluno.
   * @return array
   */
  protected function _getComponentesCursados()
  {
    return array_diff(
      array_keys($this->_getComponenteCurricular()),
      array_keys($this->_getDispensaDisciplina())
    );
  }

  /**
   * @return Avaliacao_Model_NotaAluno
   */
  protected function _getNotaAluno()
  {
    $matricula = $this->_getConfigOption('matricula', 'cod_matricula');

    return array('instance' => new Avaliacao_Model_NotaAluno(array(
      'id'        => 1,
      'matricula' => $matricula
    )));
  }

  /**
   * @return array
   */
  protected function _getFaltaAluno()
  {
    $matricula = $this->_getConfigOption('matricula', 'cod_matricula');

    return array('instance' => new Avaliacao_Model_FaltaAluno(array(
      'id'        => 1,
      'matricula' => $matricula,
      'tipoFalta' => $this->_getRegraOption('tipoPresenca')
    )));
  }

  /**
   * @return array
   */
  protected function _getParecerDescritivoAluno()
  {
    $matricula = $this->_getConfigOption('matricula', 'cod_matricula');

    return array('instance' => new Avaliacao_Model_ParecerDescritivoAluno(array(
      'id'                => 1,
      'matricula'         => $matricula,
      'parecerDescritivo' => $this->_getRegraOption('parecerDescritivo')
    )));
  }

  /**
   * @return clsPmieducarMatricula
   */
  protected function _setUpMatriculaMock()
  {
    $mock = $this->getCleanMock('clsPmieducarMatricula');

    $mock->expects($this->any())
              ->method('detalhe')
              ->will($this->returnValue($this->_getConfigOptions('matricula')));

    CoreExt_Entity::addClassToStorage('clsPmieducarMatricula',
      $mock, NULL, TRUE
    );

    $this->mockDbPreparedQuery([[
        'serie_regra_avaliacao_id' => 1,
        'ref_ref_cod_escola' => 1,
        'ref_cod_curso'      => 1,
        'ref_cod_turma'      => 1,
        'ref_cod_aluno'      => 1,
        'ref_ref_cod_serie'  => 1,
        'ano'                => 2009,
        'serie_carga_horaria'=> 800,
        'curso_hora_falta'   => 250 / 300,
        'escola_utiliza_regra_diferenciada' => null,
        'dependencia'        => null,
        'aprovado'           => null,
        'curso_carga_horaria'=> null,
        'serie_dias_letivos' => null
    ]]);

    return $this;
  }

  public function mockDbPreparedQuery($return)
  {
      Portabilis_Utils_Database::$_db = $this->getDbMock();

      $execPreparedQuery = Portabilis_Utils_Database::$_db->expects($this->any())
          ->method('execPreparedQuery')
          ->will($this->returnValue(true));

      $returnCallback = function($reset = false) use ($return) {
          static $total = 0;
          if($reset) {
              $total = 0;
              return false;
          }
          if($total == count($return)-1) {
              return ++$total;
          }
          return false;
      };
      $returnCallback(true);
      Portabilis_Utils_Database::$_db->expects($this->any())
          ->method('ProximoRegistro')
          ->will($this->returnCallback($returnCallback));

      Portabilis_Utils_Database::$_db->expects($this->any())
          ->method('Tupla')
          ->will($this->returnCallback(function() use ($return) {
              static $total = 0;
              return $return[$total++];
          }));
  }

  /**
   * @return clsPmieducarMatriculaTurma
   */
  protected function _setUpMatriculaTurmaMock()
  {
    $mock = $this->getCleanMock('clsPmieducarMatriculaTurma');

    $mock->expects($this->any())
        ->method('lista')
        ->with(1)
        ->will($this->returnValue($this->_getConfigOptions('matriculaTurma')));

    CoreExt_Entity::addClassToStorage('clsPmieducarMatriculaTurma',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarSerie
   */
  protected function _setUpSerieMock()
  {
    $mock = $this->getCleanMock('clsPmieducarSerie');

    $mock->expects($this->any())
         ->method('detalhe')
         ->will($this->returnValue($this->_getConfigOptions('serie')));

    CoreExt_Entity::addClassToStorage('clsPmieducarSerie',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarCurso
   */
  protected function _setUpCursoMock()
  {
    $mock = $this->getCleanMock('clsPmieducarCurso');

    $mock->expects($this->any())
         ->method('detalhe')
         ->will($this->returnValue($this->_getConfigOptions('curso')));

    CoreExt_Entity::addClassToStorage('clsPmieducarCurso',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarEscolaAnoLetivo
   */
  protected function _setUpEscolaAnoLetivo()
  {
    $mock = $this->getCleanMock('clsPmieducarEscolaAnoLetivo');

    $mock->expects($this->any())
         ->method('lista')
         ->with(1, 2009, NULL, NULL, 1, NULL, NULL, NULL, NULL, 1)
         ->will($this->returnValue($this->_getConfigOptions('escolaAnoLetivo')));

    CoreExt_Entity::addClassToStorage('clsPmieducarEscolaAnoLetivo',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarAnoLetivoModulo
   */
  protected function _setUpAnoLetivoModulo()
  {
    $mock = $this->getCleanMock('clsPmieducarAnoLetivoModulo');

    $mock->expects($this->any())
         ->method('lista')
         ->with(2009, 1)
         ->will($this->returnValue($this->_getConfigOptions('anoLetivoModulo')));

    CoreExt_Entity::addClassToStorage('clsPmieducarAnoLetivoModulo',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarModulo
   */
  protected function _setUpModulo()
  {
    $mock = $this->getCleanMock('clsPmieducarModulo');

    $mock->expects($this->any())
         ->method('detalhe')
         ->will($this->returnValue($this->_getConfigOptions('modulo')));

    CoreExt_Entity::addClassToStorage('clsPmieducarModulo', $mock, NULL, TRUE);

    return $this;
  }

  /**
   * @return clsPmieducarEscolaSerieDisciplina
   */
  protected function _setUpEscolaSerieDisciplinaMock()
  {
    $mock = $this->getCleanMock('clsPmieducarEscolaSerieDisciplina');

    $mock->expects($this->any())
         ->method('lista')
         ->will($this->returnValue($this->_getConfigOptions('escolaSerieDisciplina')));

    CoreExt_Entity::addClassToStorage('clsPmieducarEscolaSerieDisciplina',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * @return clsPmieducarDispensaDisciplina
   */
  protected function _setUpDispensaDisciplinaMock()
  {
    $mock = $this->getCleanMock('clsPmieducarDispensaDisciplina');

    $mock->expects($this->any())
        ->method('disciplinaDispensadaEtapa')
        ->will($this->returnValue($this->_getConfigOptions('dispensaDisciplina')));

    CoreExt_Entity::addClassToStorage('clsPmieducarDispensaDisciplina',
      $mock, NULL, TRUE
    );

    return $this;
  }

  /**
   * Configura e retorna um mock de RegraAvaliacaoDataMapper que retorna uma
   * instância de RegraAvaliacao_Model_Regra configurada de acordo com as
   * opções do array $_regraOptions.
   *
   * @return RegraAvaliacao_Model_RegraDataMapper
   */
  protected function _getRegraDataMapperMock()
  {
    $regraAvaliacao = new RegraAvaliacao_Model_Regra($this->_regraOptions);

    $mock = $this->getCleanMock('RegraAvaliacao_Model_RegraDataMapper');
    $mock->expects($this->any())
         ->method('find')
         ->with(1)
         ->will($this->returnValue($regraAvaliacao));

    return $mock;
  }

  /**
   * Configura uma das opções a serem passadas durante a instanciação de
   * RegraAvaliacao_Model_Regra.
   *
   * @param string $key
   * @param mixed $value
   */
  protected function _setRegraOption($key, $value)
  {
    if (!array_key_exists($key, $this->_regraOptions)) {
      require_once 'CoreExt/Exception/InvalidArgumentException.php';
      throw new CoreExt_Exception_InvalidArgumentException('regraOption:' . $key);
    }

    $this->_regraOptions[$key] = $value;
    return $this;
  }

  /**
   * Getter.
   *
   * @param string $key
   * @return mixed
   */
  protected function _getRegraOption($key)
  {
    if (!array_key_exists($key, $this->_regraOptions)) {
      require_once 'CoreExt/Exception/InvalidArgumentException.php';
      throw new CoreExt_Exception_InvalidArgumentException('regraOption: ' . $key);
    }

    return $this->_regraOptions[$key];
  }

  /**
   * @return FormulaMedia_Model_Formula
   */
  protected function _setUpFormulaMedia()
  {
    return new FormulaMedia_Model_Formula(array(
      'id'           => 1,
      'nome'         => 'Média aritmética',
      'formulaMedia' => 'Se / Et',
      'tipoFormula'  => FormulaMedia_Model_TipoFormula::MEDIA_FINAL
    ));
  }

  /**
   * @return FormulaMedia_Model_Formula
   */
  protected function _setUpFormulaRecuperacao()
  {
    return new FormulaMedia_Model_Formula(array(
      'id'           => 1,
      'nome'         => 'Média ponderada',
      'formulaMedia' => '(Se / Et * 0.6) + (Rc * 0.4)',
      'tipoFormula'  => FormulaMedia_Model_TipoFormula::MEDIA_RECUPERACAO
    ));
  }

  /**
   * @return TabelaArredondamento_Model_Tabela
   */
  protected function _setUpTabelaArredondamentoConceitual()
  {
      // Valores padrão dos atributos de TabelaArredondamento_Model_TabelaValor
      $data = array(
          'tabelaArredondamento' => 2,
          'nome'                 => NULL,
          'descricao'            => NULL,
          'valorMinimo'          => -1,
          'valorMaximo'          => 0
      );

      $tabelaValores = array();

      for ($i = 0; $i <= 10; $i++) {
          $data['nome'] = $i;
          $data['valorMinimo'] += 1;
          $data['valorMaximo'] += 1;

          if ($i == 10) {
              $data['valorMinimo'] = 9;
              $data['valorMaximo'] = 10;
          }

          $tabelaValores[$i] = new TabelaArredondamento_Model_TabelaValor($data);
      }

      $mock = $this->getCleanMock('TabelaArredondamento_Model_TabelaValorDataMapper');
      $mock->expects($this->any())
          ->method('findAll')
          ->will($this->returnValue($tabelaValores));

      $tabelaDataMapper = new TabelaArredondamento_Model_TabelaDataMapper();
      $tabelaDataMapper->setTabelaValorDataMapper($mock);

      $tabela = new TabelaArredondamento_Model_Tabela(array('nome' => 'Numéricas'));
      $tabela->setDataMapper($tabelaDataMapper);

      return $tabela;
  }

  /**
   * @return TabelaArredondamento_Model_Tabela
   */
  protected function _setUpTabelaArredondamento()
  {
    // Valores padrão dos atributos de TabelaArredondamento_Model_TabelaValor
    $data = array(
      'tabelaArredondamento' => 1,
      'nome'                 => NULL,
      'descricao'            => NULL,
      'valorMinimo'          => -1,
      'valorMaximo'          => 0
    );

    $tabelaValores = array();

    for ($i = 0; $i <= 10; $i++) {
      $data['nome'] = $i;
      $data['valorMinimo'] += 1;
      $data['valorMaximo'] += 1;

      if ($i == 10) {
        $data['valorMinimo'] = 9;
        $data['valorMaximo'] = 10;
      }

      $tabelaValores[$i] = new TabelaArredondamento_Model_TabelaValor($data);
    }

    $mock = $this->getCleanMock('TabelaArredondamento_Model_TabelaValorDataMapper');
    $mock->expects($this->any())
         ->method('findAll')
         ->will($this->returnValue($tabelaValores));

    $tabelaDataMapper = new TabelaArredondamento_Model_TabelaDataMapper();
    $tabelaDataMapper->setTabelaValorDataMapper($mock);

    $tabela = new TabelaArredondamento_Model_Tabela(array('nome' => 'Numéricas'));
    $tabela->setDataMapper($tabelaDataMapper);

    return $tabela;
  }

  protected function _setComponenteDataMapperMock(ComponenteCurricular_Model_ComponenteDataMapper $mapper)
  {
    $this->_componenteDataMapperMock = $mapper;
    return $this;
  }

  protected function _getComponenteDataMapperMock()
  {
    if (is_null($this->_componenteDataMapperMock)) {
      $componentes = $this->_getConfigOptions('componenteCurricular');

      // Mock para ComponenteCurricular_Model_ComponenteDataMapper
      $mock = $this->getCleanMock('ComponenteCurricular_Model_ComponenteDataMapper');
      $mock->expects($this->any())
           ->method('findComponenteCurricularAnoEscolar')
           ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $componentes));

      $this->_setComponenteDataMapperMock($mock);
    }

    return $this->_componenteDataMapperMock;
  }

  protected function _setComponenteTurmaDataMapperMock(ComponenteCurricular_Model_TurmaDataMapper $mapper)
  {
    $this->_componenteTurmaDataMapperMock = $mapper;
    return $this;
  }

  protected function _getComponenteTurmaDataMapperMock()
  {
    if (is_null($this->_componenteTurmaDataMapperMock)) {
      $componentes = $this->_getConfigOptions('componentesTurma');

      // Mock para ComponenteCurricular_Model_TurmaDataMapper
      $mock = $this->getCleanMock('ComponenteCurricular_Model_TurmaDataMapper');
      $mock->expects($this->any())
           ->method('findAll')
           ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $componentes));

      $this->_setComponenteTurmaDataMapperMock($mock);
    }

    return $this->_componenteTurmaDataMapperMock;
  }

  protected function _setNotaAlunoDataMapperMock(Avaliacao_Model_NotaAlunoDataMapper $mapper = NULL)
  {
    $this->_notaAlunoDataMapperMock = $mapper;
    return $this;
  }

  protected function _getNotaAlunoDataMapperMock()
  {
    if (is_null($this->_notaAlunoDataMapperMock)) {
      $notaAluno = $this->_getConfigOption('notaAluno', 'instance');

      $mock = $this->getCleanMock('Avaliacao_Model_NotaAlunoDataMapper');
      $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('matricula' => $notaAluno->matricula))
           ->will($this->returnValue(array($notaAluno)));

      $this->_setNotaAlunoDataMapperMock($mock);
    }

    return $this->_notaAlunoDataMapperMock;
  }

  protected function _setNotaComponenteDataMapperMock(Avaliacao_Model_NotaComponenteDataMapper $mapper)
  {
    $this->_notaComponenteDataMapperMock = $mapper;
    return $this;
  }

  protected function _getNotaComponenteDataMapperMock()
  {
    if (is_null($this->_notaComponenteDataMapperMock)) {
      $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteDataMapper');
      $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('notaAluno' => $this->_getConfigOption('matricula', 'cod_matricula')), array('etapa' => 'ASC'))
           ->will($this->returnValue(array()));

      $this->_setNotaComponenteDataMapperMock($mock);
    }

    return $this->_notaComponenteDataMapperMock;
  }

  protected function _setNotaComponenteMediaDataMapperMock(Avaliacao_Model_NotaComponenteMediaDataMapper $mapper)
  {
    $this->_notaComponenteMediaDataMapperMock = $mapper;
    return $this;
  }

  protected function _getNotaComponenteMediaDataMapperMock()
  {
    if (is_null($this->_notaComponenteMediaDataMapperMock)) {
      $notaAluno = $this->_getConfigOption('notaAluno', 'instance');

      $mock = $this->getCleanMock('Avaliacao_Model_NotaComponenteMediaDataMapper');
      $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('notaAluno' => $notaAluno->id))
           ->will($this->returnValue(array()));

      $this->_setNotaComponenteMediaDataMapperMock($mock);
    }

    return $this->_notaComponenteMediaDataMapperMock;
  }

  protected function _setFaltaAlunoDataMapperMock(Avaliacao_Model_FaltaAlunoDataMapper $mapper = NULL)
  {
    $this->_faltaAlunoDataMapperMock = $mapper;
    return $this;
  }

  protected function _getFaltaAlunoDataMapperMock()
  {
    if (is_null($this->_faltaAlunoDataMapperMock)) {
      $faltaAluno = $this->_getConfigOption('faltaAluno', 'instance');

      $mock = $this->getCleanMock('Avaliacao_Model_FaltaAlunoDataMapper');
      $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('matricula' => $this->_getConfigOption('matricula', 'cod_matricula')))
           ->will($this->returnValue(array($faltaAluno)));

      $this->_setFaltaAlunoDataMapperMock($mock);
    }

    return $this->_faltaAlunoDataMapperMock;
  }

  protected function _setFaltaAbstractDataMapperMock(Avaliacao_Model_FaltaAbstractDataMapper $mapper)
  {
    $this->_faltaAbstractDataMapperMock = $mapper;
    return $this;
  }

  protected function _getFaltaAbstractDataMapperMock()
  {
    $faltaAluno = $this->_getConfigOption('faltaAluno', 'instance');

    if (is_null($this->_faltaAbstractDataMapperMock)) {
      $mock = $this->getCleanMock('Avaliacao_Model_FaltaAbstractDataMapper');
      $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('faltaAluno' => $faltaAluno->id), array('etapa' => 'ASC'))
           ->will($this->returnValue(array()));

      $this->_setFaltaAbstractDataMapperMock($mock);
    }

    return $this->_faltaAbstractDataMapperMock;
  }

  protected function _setParecerDescritivoAlunoDataMapperMock(Avaliacao_Model_ParecerDescritivoAlunoDataMapper $mapper)
  {
    $this->_parecerDescritivoAlunoDataMapperMock = $mapper;
    return $this;
  }

  protected function _getParecerDescritivoAlunoDataMapperMock()
  {
    if (is_null($this->_parecerDescritivoAlunoDataMapperMock)) {
      $parecerAluno = $this->_getConfigOption('parecerDescritivoAluno', 'instance');

      $mock = $this->getCleanMock('Avaliacao_Model_ParecerDescritivoAlunoDataMapper');

      if ($this->_getRegraOption('parecerDescritivo') != RegraAvaliacao_Model_TipoParecerDescritivo::NENHUM) {
        $matcher = $this->at(0);

        $mock->expects($matcher)
             ->method('findAll')
             ->with(array(), array('matricula' => $this->_getConfigOption('matricula', 'cod_matricula')))
             ->will($this->returnValue(array($parecerAluno)));
      }

      $this->_setParecerDescritivoAlunoDataMapperMock($mock);
    }

    return $this->_parecerDescritivoAlunoDataMapperMock;
  }

  protected function _setParecerDescritivoAbstractDataMapperMock(Avaliacao_Model_ParecerDescritivoAbstractDataMapper $mapper)
  {
    $this->_parecerDescritivoAbstractDataMapperMock = $mapper;
    return $this;
  }

  protected function _getParecerDescritivoAbstractDataMapperMock()
  {
    if (is_null($this->_parecerDescritivoAbstractDataMapperMock)) {
      $parecerAluno = $this->_getConfigOption('parecerDescritivoAluno', 'instance');

      $mock = $this->getCleanMock('Avaliacao_Model_ParecerDescritivoAbstractDataMapper');

      if ($this->_getRegraOption('parecerDescritivo') != RegraAvaliacao_Model_TipoParecerDescritivo::NENHUM) {
        $mock->expects($this->at(0))
           ->method('findAll')
           ->with(array(), array('parecerDescritivoAluno' => $parecerAluno->id), array('etapa' => 'ASC'))
           ->will($this->returnValue(array()));
      }

      $this->_setParecerDescritivoAbstractDataMapperMock($mock);
    }

    return $this->_parecerDescritivoAbstractDataMapperMock;
  }
}