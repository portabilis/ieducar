var $j = jQuery.noConflict();

(function($){

  $(function(){

    function safeToUpperCase(value){

      if (typeof(value) == 'string')
        value = value.toUpperCase();

      return value;
    }

    function safeLog(value)
    {
      if(typeof(console) != 'undefined' && typeof(console.log) == 'function')
        console.log(value);
    }

    function utf8Decode(s){
      try{
          return decodeURIComponent(escape(s));
      }
      catch(e){
          //safeLog('Erro ao decodificar string utf8: ' + s);
          return s;
      }
    }

    var $formFilter = $('#formcadastro');
    var $submitButton = $('#botao_busca');
    var $resultTable = $('#form_resultado .tablelistagem').addClass('horizontal-expand');
    $resultTable.children().remove();

    $('<div />').attr('id', 'first-bar-action')
                .attr('class', 'bar-action hide-on-search')
                .prependTo($resultTable.parent());

    $('<div />').attr('id', 'second-bar-action')
                .attr('class', 'bar-action hide-on-search')
                .appendTo($resultTable.parent());

    var $barActions = $('.bar-action').hide();

    var $selectAllButton = $('<input class="selecionar disable-on-apply-changes" type="button" value="Selecionar todos" />');
    $selectAllButton.appendTo($barActions);
    var $actionButton = $('<input class="processar disable-on-apply-changes" type="button" value="Processar" />');
    $actionButton.appendTo($barActions);

    var PageUrlBase = 'processamento';
    var ApiUrlBase = 'processamentoApi';

    var $resourceOptionsTable = $('#resource-options');
    $resourceOptionsTable.find('tr:even').addClass('even');
    $resourceOptionsTable.hide().prependTo($formFilter.parent()); 

    var changeStateFieldManual = function($containerElementId, $targetElementId){
      if ($($containerElementId).val() == 'informar-manualmente')
        $($targetElementId).show().removeAttr('disabled');
      else
        $($targetElementId).hide().attr('disabled', 'disabled');
    };

    $resourceOptionsTable.find('#percentual-frequencia').change(function(){
      changeStateFieldManual('#percentual-frequencia', '#percentual-frequencia-manual');
    });

    $resourceOptionsTable.find('#notas').change(function(){
      changeStateFieldManual('#notas', '#notas-manual');
    });

    $resourceOptionsTable.find('#faltas').change(function(){
      changeStateFieldManual('#faltas', '#faltas-manual');
    });

    $('.disable-on-search').attr('disabled', 'disabled');
    $('.hide-on-search').hide();

    $('#ref_cod_curso').change(function(){
      $('.clear-on-change-curso').val('');
    });

    var $navActions = $('<p />').attr('id', 'nav-actions');
    $navActions.prependTo($formFilter.parent()); 

    var $tableSearchDetails = $('<table />')
                              .attr('id', 'search-details')
                              .addClass('styled')
                              .addClass('horizontal-expand')
                              .addClass('center')
                              .hide()
                              .prependTo($formFilter.parent());

    var $feedbackMessages = $('<div />').attr('id', 'feedback-messages').appendTo($formFilter.parent());

    function fixupFieldsWidth(){
      var maxWidth = 0;
      var $fields = $('#formcadastro select');

      //get maxWidh
      $.each($fields, function(index, value){
        $value = $(value);
        if ($value.width() > maxWidth)
          maxWidth = $value.width(); 
      });

      //set maxWidth
      $.each($fields, function(index, value){
        $(value).width(maxWidth);
      });
    };
    fixupFieldsWidth();

    //url builders
    var resourceUrlBuilder = {
      buildUrl : function(urlBase, vars){

        _vars = '';
        for(varName in vars){
          _vars += '&'+varName+'='+vars[varName];
        }
        return urlBase + '?' + _vars;
      }
    };


    var deleteResourceUrlBuilder = {
      buildUrl : function(urlBase, resourceName, additionalVars){

        var vars = {
          att : resourceName,
          oper : 'delete',
          instituicao_id : $('#ref_cod_instituicao').val(),
          matricula_id : ''
        };

        return resourceUrlBuilder.buildUrl(urlBase, $.extend(vars, additionalVars));
      }
    };


    var postResourceUrlBuilder = {
      buildUrl : function(urlBase, resourceName, additionalVars){

        var vars = {
          att : resourceName,
          oper : 'post',
          instituicao_id : $('#ref_cod_instituicao').val(),
          matricula_id : ''
        };

        return resourceUrlBuilder.buildUrl(urlBase, $.extend(vars, additionalVars));
      }
    };


    var getResourceUrlBuilder = {
      buildUrl : function(urlBase, resourceName, additionalVars){

        var vars = {
          att : resourceName,
          oper : 'get',
          instituicao_id : $('#ref_cod_instituicao').val(),
          escola_id : $('#ref_cod_escola').val(),
          curso_id : $('#ref_cod_curso').val(),
          serie_id : $('#ref_ref_cod_serie').val(),
          turma_id : $('#ref_cod_turma').val(),
          ano : $('#ano').val(),
          etapa : $('#etapa').val()
        };

        return resourceUrlBuilder.buildUrl(urlBase, $.extend(vars, additionalVars));

      }
    };


    function changeResource($resourceElement, postFunction, deleteFunction){
      if ($.trim($resourceElement.val())  == '')
        deleteFunction($resourceElement);
      else
        postFunction($resourceElement);
    };

    var changeResourceName = function(event){
      changeResource($(this), postFalta, deleteFalta);
    };

    function validatesIfValueIsNumeric(value, targetId){
      var isNumeric = $.isNumeric(value);

      if (! isNumeric)
        handleMessages([{type : 'error', msg : 'Informe um numero válido.'}], targetId);

      return isNumeric;
    }  

    function validatesIfNumericValueIsInRange(value, targetId, initialRange, finalRange){

      if (! $.isNumeric(value) || value < initialRange || value > finalRange)
      {
        handleMessages([{type : 'error', msg : 'Informe um valor entre ' + initialRange + ' e ' + finalRange}], targetId);
        return false;
      }
      return true;
    }

    
    function postResource(options, errorCallback){
      $.ajax(options).error(errorCallback);
    }


    function updateFieldSituacao(linkToHistorico, matricula_id, situacao){
      if(situacao)
        $('#situacao-matricula-' + matricula_id).html(getLinkToHistorico(linkToHistorico, utf8Decode(situacao)));
    } 


    //callback handlers

    //delete
    function handleDelete(dataResponse){
      var targetId = dataResponse.att + '-matricula-' + dataResponse.matricula_id;
      handleMessages(dataResponse.msgs, targetId);
      updateFieldSituacao(dataResponse.link_to_historico, dataResponse.matricula_id, dataResponse.situacao_historico);
    }


    function handleErrorDeleteResource(response){
      handleMessages([{type : 'error', msg : 'Erro ao alterar recurso, detalhes:' + response.responseText}], '');
      safeLog(response);
    }

    //post
    function handleErrorPost(response){
      handleMessages([{type : 'error', msg : 'Erro ao alterar recurso, detalhes:' + response.responseText}], '');
      safeLog(response);
    }


    function handleMessages(messages, targetId){

      var hasErrorMessages = false;
      var hasSuccessMessages = false;

      //se nao é um elemento (é uma string) e o id nao inicia com #
      if (targetId && typeof(targetId) == 'string' && targetId[0] != '#')
        var $targetElement = $('#'+targetId);
      else
        var $targetElement = $(targetId || '');

      for (var i = 0; i < messages.length; i++){

        if (messages[i].type == 'success')
          var delay = 2000;
        else if (messages[i].type != 'error')
          var delay = 10000;
        else
          var delay = 60000;

        $('<p />').addClass(messages[i].type).html(messages[i].msg).appendTo($feedbackMessages).delay(delay).fadeOut(function(){$(this).remove()}).data('target_id', targetId);

        if (! hasErrorMessages && messages[i].type == 'error')
          hasErrorMessages = true;
        else if(! hasSuccessMessages && messages[i].type == 'success')
          hasSuccessMessages = true;
      }

      if($targetElement){
        if (hasErrorMessages)
          $targetElement.addClass('error').removeClass('success');
        else if (hasSuccessMessages)
          $targetElement.addClass('success').removeClass('error');
        else
          $targetElement.removeClass('success').removeClass('error');
      }
    }


    function setTableSearchDetails(dataDetails){
      $('<caption />').html('<strong>Proccessamento histórico</strong>').appendTo($tableSearchDetails);

      //set headers table
      var $linha = $('<tr />');
      $('<th />').html('Ano').appendTo($linha);
      $('<th />').html('Escola').appendTo($linha);
      $('<th />').html('Curso').appendTo($linha);
      $('<th />').html('Serie').appendTo($linha);
      $('<th />').html('Turma').appendTo($linha);
      $('<th />').html('Matricula').appendTo($linha);

      $linha.appendTo($tableSearchDetails);

      var $linha = $('<tr />').addClass('even');

      $('<td />').html($('#ano').val()).appendTo($linha);

      //field escola pode ser diferente de select caso usuario comum 
      var $htmlEscolaField = $('#ref_cod_escola').children("[selected='selected']").html() ||
                             $j('#tr_nm_escola span:last').html();
      $('<td />').html(safeToUpperCase($htmlEscolaField)).appendTo($linha);

      $('<td />').html(safeToUpperCase($('#ref_cod_curso').children("[value!=''][selected='selected']").html()  || 'Todos')).appendTo($linha);
      $('<td />').html(safeToUpperCase($('#ref_ref_cod_serie').children("[value!=''][selected='selected']").html()  || 'Todas')).appendTo($linha);
      $('<td />').html(safeToUpperCase($('#ref_cod_turma').children("[value!=''][selected='selected']").html()  || 'Todas')).appendTo($linha);
      $('<td />').html(safeToUpperCase($('#ref_cod_matricula').children("[value!=''][selected='selected']").html() || 'Todas')).appendTo($linha);
     
      $linha.appendTo($tableSearchDetails);
      $tableSearchDetails.show();

      $tableSearchDetails.data('details', dataDetails);
    }

    //exibe formulário nova consulta
    function showSearchForm(event){
      //$(this).hide();
      $navActions.html('');
      $tableSearchDetails.children().remove();
      $resultTable.children().fadeOut('fast').remove();
      $formFilter.fadeIn('fast', function(){
        $(this).show()
      });
      //$barActions.hide();
      //$resourceOptionsTable.hide();
      $('.disable-on-search').attr('disabled', 'disabled');
      $('.hide-on-search').hide();
    }


    function showNewSearchButton(){
      $navActions.html(
        $("<a href='#'>Nova consulta</a>")
        .bind('click', showSearchForm)
        .attr('style', 'text-decoration: underline')
      );
      //$barActions.show();
      //$resourceOptionsTable.show();
      $('.disable-on-search').removeAttr('disabled');
      $('.hide-on-search').show();
    }

/*    function showSearchButton(){
      $navActions.html(
        $("<a href='#'>Nova consulta</a>")
        .bind('click', showSearchForm)
        .attr('style', 'text-decoration: underline')
      );
    }
*/

    function getLinkToHistorico(link, text){
      if (link)
        return $('<a target="__blank" style="text-decoration:underline;" href='+link+'>'+text+'</a>');
      else
        return text;
    }


    function handleMatriculasSearch(dataResponse){ 

      showNewSearchButton();

      try{      
        handleMessages(dataResponse.msgs);

        if(! $.isArray(dataResponse.matriculas))
        {
           $('<td />')
            .html('As matriculas n&#227;o poderam ser recuperadas, verifique as mensagens de erro ou tente <a alt="Recarregar página" href="/" style="text-decoration:underline">recarregar</a>.')
            .addClass('center')
            .appendTo($('<tr />').appendTo($resultTable));
        }
        else if (dataResponse.matriculas.length < 1)
        {
           $('<td />')
            .html('Sem matriculas em andamento nesta turma.')
            .addClass('center')
            .appendTo($('<tr />').appendTo($resultTable));
        }
        else
        {
          setTableSearchDetails();
          //set headers
          var $linha = $('<tr />');
          $('<th />').html('Selecionar').appendTo($linha);
          $('<th />').html('Curso').appendTo($linha);
          $('<th />').html('Série').appendTo($linha);
          $('<th />').html('Turma').appendTo($linha);
          $('<th />').html('Matricula').appendTo($linha);
          $('<th />').html('Aluno').appendTo($linha);
          $('<th />').html('Situa&#231;&#227;o').appendTo($linha);
          $linha.appendTo($resultTable);

          //set rows
          $.each(dataResponse.matriculas, function(index, value){

            var $checkbox = $('<input />')
                            .attr('type', 'checkbox')
                            .attr('name', 'processar-matricula')
                            .attr('value', 'sim')
                            .attr('id', 'matricula-' + value.matricula_id)
                            .attr('class', 'matricula disable-on-apply-changes')
                            .data('matricula_id', value.matricula_id);

            var $linha = $('<tr />');
            $('<td />').html($checkbox).addClass('center').appendTo($linha);
            $('<td />').html(value.nome_curso).addClass('center').appendTo($linha);
            $('<td />').html(utf8Decode(value.nome_serie)).addClass('center').appendTo($linha);
            $('<td />').html(utf8Decode(value.nome_turma)).addClass('center').appendTo($linha);
            $('<td />').html(value.matricula_id).addClass('center').appendTo($linha);
            $('<td />').html(value.aluno_id + " - " + safeToUpperCase(value.nome)).appendTo($linha);

            var $htmlSituacao = getLinkToHistorico(value.link_to_historico, utf8Decode(value.situacao_historico));
            $('<td />').html($htmlSituacao).attr('id', 'situacao-matricula-' + value.matricula_id).addClass('center').appendTo($linha);

            $linha.fadeIn('slow').appendTo($resultTable);
          });//fim each matriculas

          $resultTable.find('tr:even').addClass('even');
          $resultTable.addClass('styled').find('checkbox:first').focus();
        }
      }
      catch(error){
        showNewSearchButton();

        handleMessages([{type : 'error', msg : 'Ocorreu um erro ao exibir as matriculas, por favor tente novamente, detalhes: ' + error}], '');

        safeLog(dataResponse);
      }
    }

    function handleErrorMatriculasSearch(response){
      showNewSearchButton();

      handleMessages([{type : 'error', msg : 'Ocorreu um erro ao carregar as matriculas, por favor tente novamente, detalhes:' + response.responseText}], '');

      safeLog(response);
    }

    //change submit button
    var onClickSearchEvent = function(event){
      if (validatesPresenseOfValueInRequiredFields())
      {
        matriculasSearchOptions.url = getResourceUrlBuilder.buildUrl(ApiUrlBase, 'matriculas', {matricula_id : $('#ref_cod_matricula').val()});

        if (window.history && window.history.pushState)
          window.history.pushState('', '', getResourceUrlBuilder.buildUrl(PageUrlBase, 'matriculas'));

        $resultTable.children().fadeOut('fast').remove();

        $formFilter.submit();
        $formFilter.fadeOut('fast');
        $navActions
          .html('Aguarde, carregando...')
          .attr('style', 'text-align:center;')
          .unbind('click');
      }
    };
    $submitButton.val('Carregar');
    $submitButton.attr('onclick', '');
    $submitButton.click(onClickSearchEvent);

    //config form search
    var matriculasSearchOptions = {
      url : '',
      dataType : 'json',
      success : handleMatriculasSearch,
      error : handleErrorMatriculasSearch
    };

    $formFilter.ajaxForm(matriculasSearchOptions);

    var onClickActionEvent = function(event){

      var $firstChecked = $('input.matricula:checked:first');

      if ($firstChecked.length < 1)
        alert('Selecione ao menos uma matrícula.');
      else{

        var additionalFields = [$('#percentual-frequencia-manual').get(0),
                                $('#notas-manual').get(0), 
                                $('#faltas-manual').get(0)
        ];

        if (validatesPresenseOfValueInRequiredFields(additionalFields)){

          var isValid = validatesIfValueIsNumeric($('#dias-letivos').val(), 'dias-letivos');

          if (isValid && $('#percentual-frequencia').val() != 'buscar-boletim')
            isValid = validatesIfNumericValueIsInRange($('#percentual-frequencia-manual').val(), '#percentual-frequencia-manual', 0, 100);

          if (isValid && $('#faltas').val() != 'buscar-boletim')
            isValid = validatesIfNumericValueIsInRange($('#faltas-manual').val(), '#faltas-manual', 0, 999);


          if (isValid){
            $('.disable-on-apply-changes').attr('disabled', 'disabled');
            $actionButton.val('Aguarde processando...');
            postProcessamento($firstChecked);
          }
        }
      }
    };

    function postProcessamento($resourceElement){

      //#TODO validar campos que usuário preenche

      var percentualFrequencia = $('#percentual-frequencia').val() == 'buscar-boletim' ? 'buscar-boletim' : $('#percentual-frequencia-manual').val();
      var faltas = $('#faltas').val() == 'buscar-boletim' ? 'buscar-boletim' : $('#faltas-manual').val();
      var notas = $('#notas').val() == 'buscar-boletim' ? 'buscar-boletim' : $('#notas-manual').val();

      var options = {
        url : postResourceUrlBuilder.buildUrl(ApiUrlBase, 'processamento', {
          matricula_id : $resourceElement.data('matricula_id')
        }),
        dataType : 'json',
        data : {
          dias_letivos : $('#dias-letivos').val(),
          situacao : $('#situacao').val(),
          extra_curricular : $('#extra-curricular').is(':checked') ? 1 : 0,
          grade_curso_id : $('#grade-curso').val(),
          percentual_frequencia : percentualFrequencia,
          notas : notas,
          faltas : faltas,
          observacao : $('#observacao').val(),
          registro : $('#registro').val(),
          livro : $('#livro').val(),
          folha : $('#folha').val()
        },
        success : function(dataResponse){
          afterChangeResource($resourceElement);
          handlePostProcessamento(dataResponse);
        }
      };

      beforeChangeResource($resourceElement);
      postResource(options, handleErrorPost);
    }

    function beforeChangeResource($resourceElement){
      if ($resourceElement.siblings('img').length < 1);
        $('<img alt="loading..." src="/modules/HistoricoEscolar/Static/images/loading.gif" />').appendTo($resourceElement.parent());
    }

    function handlePostProcessamento(dataResponse){
      try{
        var $checkbox = $('matricula-' + dataResponse.matricula_id);
        var $targetElement = $j('#matricula-'+dataResponse.matricula_id).closest('tr').first();
        handleMessages(dataResponse.msgs, $targetElement);
        updateFieldSituacao(dataResponse.link_to_historico, dataResponse.matricula_id, dataResponse.situacao_historico);
      }
      catch(error){
        showNewSearchButton();
        handleMessages([{type : 'error', msg : 'Ocorreu um erro ao enviar o processamento, por favor tente novamente, detalhes: ' + error}], '');

        safeLog(dataResponse);
      }
    }


    function afterChangeResource($resourceElement){
      $resourceElement.siblings('img').remove();
      $resourceElement.attr('checked', false);

      //verifica se chegou na ultima matricula e ativa os elements desativados
      var $firstChecked = $('input.matricula:checked:first');
      if ($firstChecked.length < 1){
        $('.disable-on-apply-changes').removeAttr('disabled');
        $actionButton.val('Processar');
        alert('O processamento chegou ao fim.');
      }
      else
        postProcessamento($firstChecked);
    }

    var onClickSelectAllEvent = function(event){
      var $checked = $('input.matricula:checked');
      var $unchecked = $('input.matricula:not(:checked)');

      $checked.attr('checked', false);
      $unchecked.attr('checked', true);
    };

    $actionButton.click(onClickActionEvent);
    $selectAllButton.click(onClickSelectAllEvent);

  });
})(jQuery);
