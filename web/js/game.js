var nxtbtn = '<button id="next" class="button btn-round big is-success " type="button" onclick="nextQuestion()">' +
    '<i class="fa fa-forward"></i>' +
    '<span class="btn-round-text">Volgende</span>' +
    '</button>';

var celebrate = [   ["Hee, wat gebeurt er met me?!", "Oei ik groei"], ["Ow yeah! dit voelt veel beter.", "Ga alsjeblieft zo door wil je"],
                    ["Hee, ik zie nu veel meer. Yes!", "Wat 'n stukje glas al niet kan doen"],
                    ["Aah! Veel beter dit.", "Dit ziet er iets normaler uit"],
                    ["En, wat vind je van m'n nieuwe kapsel?", "Hoe hip ben ik!"],
                    ["Misschien is 'n ook snor wel wat ja.", "Me and my Mustache"],
                    ["Jippie! Je hebt gewonnen! Thanks!", "Je bent echt Frantastisch!!"]
                ];


var gametimer
var time_started;
var time_left;
var timeouts = [];
var skipped = 0;
var helped = 0;

function nextQuestion() {
    $.get( "nextquestion", function( data ) {
        cancelAllTimeouts();
        var elements = elementsForAnswerType(data['answer_type'], data['options'])
        $( "#word" ).html( data['word'] != '' ? data['word'] : elements[1]);
        showTutorMessage(data['tutor_output']);
        $( "#game-interactions" ).html(elements[0])
        $('#helpbtns button').prop('disabled', false);
        bindKeys();
        $('#word').removeClass();
        $('#guessf').focus();
        startCounting();
    });
}



function showAnswerResult(answer, result) {
    if(result['tutor_output'][0] != "controlgroup"){
        showTutorMessage(result['tutor_output'])
    }
    word = $( "#word" );
    word.html( result['answer'] );
    answer_is_equal = result['answer'].toLowerCase()  == result['target'].toLowerCase()
    if (skipped){
        word.html( result['answer'] ).addClass( 'altcorrect');
    } else {
        switch (result['correct']){
            case 1:
                showCorrect(answer_is_equal);
                break;
            case 2:
                showAltCorrect();
                if(result['tutor_output'][0] == "controlgroup") {
                    $('#word').append("<br><span style='color: #333'>(" + result['target'] + ")</span>");
                }
                break;
            default:
                showIncorrect(answer_is_equal);
                if(result['tutor_output'][0] == "controlgroup"){
                    $('#word').append("<br><span style='color: #333'>("+result['target']+")</span>");
                }
                break;
        }
    }
}

function showCorrect(equal) {
    $('[data-control]').html('<span><i style="color: #23d160" class="fa fa-check"></i></span>')
    $('#word').addClass(equal ? 'correct' : 'incorrect');
}

function showIncorrect(equal) {
    $('[data-control]').html('<span><i style="color: #ff3860" class="fa fa-times"></i></span>')
    $('#word').addClass(equal ? 'correct' : 'incorrect');
}

function showAltCorrect() {
    $('[data-control]').html('<span><i style="color: #209cef" class="fa fa-check-square-o"></i></span>')
    $('#word').addClass('altcorrect');
}

function showTutorMessage(tutor_output) {
    if(typeof tutor_output == "string"){
        to = JSON.parse(tutor_output);
    } else {
        to = tutor_output;
    }
    msg = to[0];
    extra = to[1];
    showSepparateSentences(msg, extra);
}

function showSepparateSentences(msg, extra) {
    tr = $('#game-response span');
    tre = $('#game-response-extra span');
    tr.parent().removeClass().addClass(msg.type + ' ' + msg.mood);
    tre.html('').parent().removeClass();
    sentences = msg.text.match(/[^\.!\?]+[\.!\?]+/g);
    $.each(sentences, function (index, s) {
        timeout = setTimeout(function () { tr.html(s); },3000*index);
        timeouts.push(timeout);
    });
    timeout = setTimeout(function () {
        if (typeof extra != 'undefined' && extra != null ){
            tre.html(extra.text).parent().removeClass().addClass('active ' + extra.type + ' ' + extra.mood);
        }
    },3000*(sentences.length-1) + 500);
    timeouts.push(timeout);
}

function showHint(text, mood) {
    hintcont = $('#game-response-extra span');
    hintcont.html(text).parent().removeClass().addClass('active '  + mood);
    timeouts.push(setTimeout(function () { hintcont.html('').parent().removeClass(); },6000));
}


function elementsForAnswerType(answer_type, data) {
    var interactions = "";
    var options = [];
    switch (answer_type){
        case "CORR_INCORR":
            interactions =  '<button class="button is-success" type="button" onclick="check(\'correct\')">goed</button> ' +
                            '<button class="button is-danger" type="button" onclick="check(\'incorrect\')">fout</button>';
            break;

        case "GUESS_SHUFF":
        case "GUESS_DOTS":
            interactions =  '<div class="field has-addons">' +
                            '<div class="control is-expanded"><input id="guessf" class="input is-primary" type="text" placeholder=""></div>' +
                            '<div class="control"><a id="send-btn" class="button is-success" type="button" onclick="checkGuessFull()"><i class="fa fa-paper-plane"></i></a></div></div>'
            break;
        case "PICK_CORR":
            interactions =  '<button id="next" class="button btn-round big is-success " type="button" onclick="checkPick()">' +
                            '<i class="fa fa-check"></i>' +
                            '<span class="btn-round-text">Controleer</span>' +
                            '</button>';
            console.log(data);
            $(data).each(function (i, word) {
                options += '<div class="pick-one"><label><input type="radio" name="pick-one" value="'+word+'"><span>'+word+'</span></label></div>'
            })
            break;
        default:
            break;
    }
    return [interactions, options];
}


function check(answer) {
    cancelAllTimeouts();
    stopCounting();
    reactiontime = Math.floor(($.now() - time_started)/1000); // time in seconds
    $('#game-interactions ,#helpbtns button').prop('disabled', true);
    word = $( "#word" ).html();
    $.post( "checkanswer",JSON.stringify({word: word, answer: answer.toLowerCase(), reactiontime: reactiontime, skipped: skipped, helped: helped }),
        function( data ) {
            showAnswerResult(answer, data)
            adjust('level', data['reward']['level']);
            adjust('score', data['reward']['score']);
            showTimeBonus(data['reward']['time_bonus']);
            changeTutor(data['reward']['level'])
            if (data['game_over'] == 1){
                gameOver();
            }else{
                $( "#game-interactions" ).html(nxtbtn);
                bindKeys();
                resetHelpAndSkipped();
            }
        }
    );
}

function checkPick() {
    var answer = $('input[name=pick-one]:checked').val();
    if (answer !='' && typeof(answer) != "undefined"){
        check(answer)
    } else {
        showHint("Selecteer een antwoord alsjeblieft");
    }

}

function checkGuessFull() {
    $answer = $('#guessf').val();
    if ($answer != '' && $answer.length >=1){
        check($answer);
    } else {
        showHint("Vul een antwoord in alsjeblieft");
    }
}



function changeTutor(level){
    if (level != 0 && level != null) {
        var tutor = $('.tutor');
        var current_level = tutor.attr('data-lvl');
        var new_level = Number(current_level) + level;
        var new_tutor_img = "/img/tutors/" + new_level + ".png";
        tutor.fadeOut(1000, function () {
            tutor.attr("src", new_tutor_img);
            tutor.attr("data-lvl", new_level);
            tutor.fadeIn(1000);
        });
        if (level > 0) {
            cancelAllTimeouts();
            msg = celebrate[new_level - 2][0];
            $('[data-tutor]').addClass('pos');
            $('[data-tutor] span').html(msg);
            showHint(celebrate[new_level - 2][1], 'pos');
        } else if (level < 0) {
            $('[data-tutor] span').html(msg).addClass('neg');
            showHint("Dit doet pijn hoor!", 'neg');
        }
        if (new_level == 8){
            gameWon();
        }
    }
}



function bindKeys() {
    unbindKeys();
    $('body').keypress(function (e) {
        var key = e.which;
        if(key == 13) {  // the enter key code
            $('#send-btn, #next').click();
            return false;
        }
    });
    $(' label ').click(function(){
        $(this).children(' span ').addClass('input-checked');
        $(this).parent('.pick-one').siblings('.pick-one').children(' label ').children(' span ').removeClass('input-checked');
    });

}

function unbindKeys() {
    $('body').off();
}

function adjust(property, amount){
    if (amount != null && amount != 0) {
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

function showTimeBonus(time_bonus) {
    if (time_bonus){
        elem = $('#score-msg');
        content = elem.html();
        updated = content + ' <i class="fa fa-rocket"></i>'
        elem.html(updated);
    }

}

function cancelAllTimeouts() {
    $.each(timeouts, function (i, timeout) {
        clearTimeout(timeout);
    })
    timeouts = [];
}

function startCounting() {
    time_started = time_started = $.now();
    gametimer =  setInterval(function () {
      countDown();  
    },1000);
}

function countDown() {
    if (typeof (time_left) == "undefined" ){
        timestr = $('#time').html().trim();
        minutessecs = timestr.split(':');
        time_left = Number(minutessecs[0])*60 + Number(minutessecs[1])
    }
    time_left--;
    mins = Math.floor(time_left / 60);
    secs = time_left - mins *60;
    extrazero = secs < 10 ? '0' : '';
    $('#time').html(mins+':'+extrazero+secs);
    if (mins < 1 && secs < 1){
        gameOver();
    }
}

function stopCounting() {
    clearInterval(gametimer);
}

function help() {
    helped = 1;
    $.post("help", function (data) {
        parsed =  JSON.parse(data);
        word = parsed['text'][0];
        if (parsed['exp']){
            showHint("Dit woord betekend in het Nederlans: <b>"+word+"</b>", 0);
        } else {
            showSepparateSentences({'text': "Vertaling: <b>"+word+"</b>.", 'mood': 0, 'type': 'HELP'},null);
        }

    });
}

function skipQuestion() {
    skipped = 1;
    check('');
}


function resetHelpAndSkipped(){
    helped = skipped = 0;
}

function gameOver() {
    playtime = Math.floor(($.now() - time_started)/1000);
    $.post("end", JSON.stringify({playtime: playtime}),function (data){
        stopCounting();
        message = {'text':"Tijd om!", 'mood' : 0 , 'type' :''};
        hint = {'text':"Zullen we het nog een keer proberen?", 'mood':0 , 'type' : ''};
        showTutorMessage([message, hint]);
        btns = '<p>Game Over</p>' +
            '<a class="button is-warning" href="/game"><span><i class="fa fa-arrow-left"></i>Menu</span></a>' +
            '<a class="button is-success" href="/game/new"><span><i class="fa fa-robot"></i>Nieuw spel</span></a>'
        $("#word").html(btns);
        $("#game-interactions").html('');
    })
}

function gameWon() {
    playtime = Math.floor(($.now() - time_started)/1000);
    $.post("end", JSON.stringify({playtime: playtime}),function (data){
        stopCounting();
        btns = '<p>Gewonnen!</p>' +
            '<a class="button is-warning" href="/game"><span><i class="fa fa-arrow-left"></i>Menu</span></a>' +
            '<a class="button is-success" href="/game/new"><span><i class="fa fa-robot"></i>Nieuw spel</span></a>'
        $("#word").html(btns);
        $("#game-interactions").html('');
    })
}

/// frontpage

$(".btn-round.main").on('click', function (e) {
    toggle = $(this).hasClass('active');
    info = $('#info')
    btns = $('.btn-round.main');
    $.each(btns, function (i, btn) {
        $(btn).removeClass('active');
    })
    if (toggle){
        info.removeClass('active');
    }else{
        info.addClass('active');
        $(this).addClass('active');
    }

})

$(function () {
    timeouts =[];
    $.each($(".btn-round.main"), function (index, btn) {
        timeouts.push(setTimeout(function () { $(btn).addClass('active'); },800*(index+1)));
        timeouts.push(setTimeout(function () { $(btn).removeClass('active'); },800*(index+1)+800));
    });
    $('.frontpage').on('click', function () {
        $.each(timeouts, function (i, timeout) {
            clearTimeout(timeout);
        })
    })
});



function showMe() {
    html = "<h1>Over mij</h1>" +
        "Leuk dat je komt kijken op mijn website! <br>" +
        "Zoals je ziet is het nogal een simpele site met 3 knoppen. Waarvan je nu dus al de eerste hebt ondekt. Heel goed! 👍 <br><br>" +
        "Aangezien je deze site hebt weten te vinden, ken je mijn naam al. Maar omdat jij het bent mag je me gewoon bij mijn voornaam - Frans - noemen.<br>" +
        "Ik ben 30 jaar oud, woon in Arnhem en werk bij <a href='http://flitsmeister.nl' target='_blank'> het meest flitsende bedrijf van Nederland</a>. <br>" +
        "Mijn vrijetijdsbesteding bestaat momenteel vooral uit het afronden van m'n studie. Meer hierover vind je onder de tweede knop.<br>" +
        "Het zou erg fijn zijn als je daar zometeen even gaat kijken. Als bedankje krijg je de mogelijkheid om proefpersoon te worden in mijn scriptieonderzoek." +
        "<br><br> Ik zou je nog veel meer wiilen vertellen, maar daar heb ik nu echt de tijd niet voor. <br> En daarbij, zo interessant ben ik nu ook weer niet." +
        "<br><br>Als je nou toch nog meer wil weten, dan zou je een poging kunnen wagen op FB, of kunnen kijken wat er onder de derde knop te vinden is. 😉" +
        "<a class='button is-info is-fb btn-round is-pulled-right' href='https://www.facebook.com/grunsfrans' target='_blank'><i class='fab fa-facebook-f'></i></a> ";
    $('#info').html(html);
}

function showThesis() {
    html = "<h1>Thesis Onderzoek</h1>" +
        "Ik ben al een tijdje bezig om mijn studie Artificial Intelligence (AI) aan de Radboud Universiteit Nijmegen af te ronden.<br>" +
        "Voor mijn Bachelorthesis heb ik een online 'educational game' (educatief spel) ontwikkeld waarmee ik het onderzoek" +
        " wil doen naar leerprestaties onder bepaalde invloeden.<br><br>" +
        "In het spel is het de bedoeling om binnen 5 minuten zoveel mogelijk Engelse woorden correct te beoordelen of te spellen.<br>" +
        "Met elk goed andwoord scoor je punten. Als je genoeg punten haalt kom je in een hoger level en worden de woorden moeilijker.<br>" +
        "Het hoogst haalbare level is 8. Lukt het jou om dit te halen? Zo niet, dan kun je het gewoon nog eens proberen.   <br><br>" +
        "Zou jij, gewoon voor de uitdaging, of anders voor de actie: \"Help Frans aan een diploma\", mee willen doen aan mijn onderzoek? <br> Dankjewel!" +
        "<br><a class='button is-warning is-medium is-pulled-right is-semi-trans ' style='margin-top: 15px'" +
        "href='/game' target='_blank'><span><i class='fa fa-graduation-cap'></i></span>Doe mee</a> ";
    $('#info').html(html);

}

function showCV() {
    html = "<h1>Curriculum Vitae</h1>" +
        "Binnenkort komt hier een mooi overzichtje van wat ik allemaal heb gedaan en kan. <br>" +
        "Voor nu zul je het met mijn linkedin moeten doen. Excuses hiervoor." +
        "<a class='button btn-round is-info is-lnkin is-pulled-right' type='button' href='https://www.linkedin.com/in/fvg1988' target='_blank'> <i class='fab fa-linkedin-in'></i></a> ";
    $('#info').html(html);
}

