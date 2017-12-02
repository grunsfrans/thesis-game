  var abbrtitle = true;


  function runAnim(e, x) {
    $(e).removeClass().addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
      $(this).removeClass();
    });
  };

  function selectRandomAnimationAndRunOn(e){
  	anim = randomAnimationFrom(randomAnimationGroup());
 	runAnim(e, anim);
  }

  function randomAnimationGroup(){
  	ar = Object.keys(animations);
  	rand = Math.floor((Math.random() * ar.length));
 	return ar[rand];
  }
  function randomAnimationFrom(group){
 	ar = animations[group];
 	rand = Math.floor((Math.random() * ar.length));
 	return ar[rand];
  }


  $(".butt-hover").mousedown(function(){
    $(this).css({backgroundColor: '#f35626', color: 'white'});
  })
  $(".butt-hover").mouseup(function(){
    $(this).css({backgroundColor: '', color: ''});
  })

 $("#site-title").click(function(){
 	title = $(this).find("h1");
 	abbrtitle ? $(title).html("FransvanGrunsven.nl") : $(title).html("FvG.nl");
 	// random = Math.floor((Math.random() * 2));
 	// if (random == 1){
 	// 	$(title).html('<i class="em em-beer" aria-hidden="true"></i>');
 	// };
 	abbrtitle = !abbrtitle;
 	selectRandomAnimationAndRunOn(this);
 })

 // $("#site-title").hover(function(){
 // 	$(".repl-inv-txt").addClass(" visible ");
 // 	$(".repl-inv-txt").addClass("zoomIn animated");
 // }, function(){
 // 	$(".repl-inv-txt").removeClass(" visible zoomIn animated");
 // })


 $(document).ready(function(){
 	selectRandomAnimationAndRunOn($("#site-title"))
 })

 animations = {
  "attention_seekers": [
    "bounce",
    "flash",
    "pulse",
    "rubberBand",
    "shake",
    "headShake",
    "swing",
    "tada",
    "wobble",
    "jello"
  ],

  "bouncing_entrances": [
    "bounceIn",
    "bounceInDown",
    "bounceInLeft",
    "bounceInRight",
    "bounceInUp"
  ],

  // "bouncing_exits": [
  //   "bounceOut",
  //   "bounceOutDown",
  //   "bounceOutLeft",
  //   "bounceOutRight",
  //   "bounceOutUp"
  // ],

  "fading_entrances": [
    "fadeIn",
    "fadeInDown",
    "fadeInDownBig",
    "fadeInLeft",
    "fadeInLeftBig",
    "fadeInRight",
    "fadeInRightBig",
    "fadeInUp",
    "fadeInUpBig"
  ],

  // "fading_exits": [
  //   "fadeOut",
  //   "fadeOutDown",
  //   "fadeOutDownBig",
  //   "fadeOutLeft",
  //   "fadeOutLeftBig",
  //   "fadeOutRight",
  //   "fadeOutRightBig",
  //   "fadeOutUp",
  //   "fadeOutUpBig"
  // ],

  "flippers": [
    "flip",
    "flipInX",
    "flipInY"
    //"flipOutX",
    //"flipOutY"
  ],

  "lightspeed": [
    "lightSpeedIn",
    "lightSpeedOut"
  ],

  "rotating_entrances": [
    "rotateIn",
    "rotateInDownLeft",
    "rotateInDownRight",
    "rotateInUpLeft",
    "rotateInUpRight"
  ],

  // "rotating_exits": [
  //   "rotateOut",
  //   "rotateOutDownLeft",
  //   "rotateOutDownRight",
  //   "rotateOutUpLeft",
  //   "rotateOutUpRight"
  // ],

  "specials": [
    "hinge",
    "jackInTheBox",
    "rollIn"
    //"rollOut"
  ],

  "zooming_entrances": [
    "zoomIn",
    "zoomInDown",
    "zoomInLeft",
    "zoomInRight",
    "zoomInUp"
  ],

  // "zooming_exits": [
  //   "zoomOut",
  //   "zoomOutDown",
  //   "zoomOutLeft",
  //   "zoomOutRight",
  //   "zoomOutUp"
  // ],

  "sliding_entrances": [
    "slideInDown",
    "slideInLeft",
    "slideInRight",
    "slideInUp"
  ]

  // "sliding_exits": [
  //   "slideOutDown",
  //   "slideOutLeft",
  //   "slideOutRight",
  //   "slideOutUp"
  // ]
}