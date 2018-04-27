var nxtbtn = '<button class="button btn-round big is-success " type="button" onclick="nextQuestion()">' +
    '<i class="fa fa-forward"></i>' +
    '<span class="btn-round-text">Volgende</span>' +
    '</button>';
var time_started


function nextQuestion() {
    $.get( "nextquestion", function( data ) {
        console.log(data);
        var elements = elementsForAnswerType(data['answer_type'])
        $( "#word" ).html( data['word']);
        showTutorMessage(data['message']);
        $( "#game-interactions" ).html(elements)
        bindKeys();
        $('#word').removeClass();
        time_started = $.now()
    });
}

function check(answer) {
    reactiontime = Math.ceil(($.now() - time_started)/1000) // time in seconds
    word = $( "#word" ).html();
    $.post( "checkanswer",JSON.stringify({word: word, answer: answer, reactiontime: reactiontime }), function( data ) {
        console.log(data);
       showAnswerResult(answer, data)
        adjust('level', data['reward']['level']);
        adjust('score', data['reward']['score']);
        $( "#game-interactions" ).html(nxtbtn);
    });
}

function showAnswerResult(answer, result) {
    showTutorMessage(result['message'], result['extra'])
    word = $( "#word" );
    word.html( result['answer'] );
    switch (result['correct']){
        case 1:
            word.addClass("correct");
            break;
        case 2:
            word.addClass("altcorrect");
            break;
        default:
            word.addClass("incorrect");
            break;
    }
}

function showTutorMessage(msg, extra) {
    tr = $('#tutor-response span');
    tre = $('#tutor-response-extra');
    tr.html(msg['text']).parent().removeClass().addClass(msg['type'] + ' ' + msg['mood']);
    tre.removeClass();
    if (typeof extra != 'undefined'){
        console.log('bgghj')
        tre.addClass('active ' + extra['type'] + ' ' + extra['mood']).html(extra['text']);
    }
}

function checkGuessFull() {
    $answer = $('#guessf').val();
    console.log($answer);
    check($answer);
}

function elementsForAnswerType(answer_type) {
    var interactions = "";
    switch (answer_type){
        case "CORRECT_INCORRECT":
            interactions =  '<button class="button is-success" type="button" onclick="check(\'correct\')">goed</button> ' +
                            '<button class="button is-danger" type="button" onclick="check(\'incorrect\')">fout</button>';
            break;

        case "GUESS_FULL":
            interactions =  '<div class="field has-addons">' +
                            '<div class="control is-expanded"><input id="guessf" class="input is-primary" type="text" placeholder=""></div>' +
                            '<div class="control"><a id="send-btn" class="button is-success" type="button" onclick="checkGuessFull()"><i class="fa fa-paper-plane"></i></a></div>' +
                            '</div>';
        default:
            break;
    }
    return interactions;
}

function bindKeys() {
    unbindKeys();
    $('#game-interactions').keypress(function (e) {
        var key = e.which;
        if(key == 13) {  // the enter key code
            $('#send-btn').click();
            return false;
        }
    });
}

function unbindKeys() {
    $('#game-interactions').off();
}

function adjust(property, amount){
    if (amount != 0) {
        var prop_elem = $('#' + property);
        var msg_elem = $('#' + property + '-msg');
        var change = (amount > 0) ? '+' + amount : amount;
        prop_elem.html(Number(prop_elem.html()) + Number(amount));
        showChange(change, msg_elem);
    }
}

function showChange(change, elem) {
    var change_class = (change[0] == '+') ? 'pos' : 'neg'
    elem.html( change );
    elem.addClass("show " + change_class).delay(2000).queue(function(){
        $(this).removeClass("show " + change_class).dequeue();
    });
}