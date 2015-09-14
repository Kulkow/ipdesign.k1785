var Itrating = {
  activeClass : 'active',
  hoverClass : 'hover',
  itemClass : 'star',
  buttonClass : 'button',
  block : null,
  block_rating : null,
  count : null,
  allow : null,
  _current : null, //0-5
  items: [],
  _init : function(star){
    if(BX.hasClass(star,this.itemClass)){
      this.block = BX.findParent(star,{'class' : 'rating'});
      this.block_rating = BX.findParent(star,{'class' : 'rating_vote'});
    }
    if(BX.hasClass(star,'rating')){
      this.block = star;
      this.block_rating = BX.findChild(star,{'class' : 'rating_vote'},true);
    }
    if(this.block){
        this.max = this.block.getAttribute('data-max');
        this._current = this.block.getAttribute('data-vote');
        this.allow = this.block.getAttribute('data-allow');
        this.items = BX.findChildren(this.block_rating,{'class':'star'});
    }
    return this;
  },
  init : function(star){
    this._init(star);
    this.SetVote(this._current,null);
    return this;
  },
  SetVote    : function(vote, star){
    if(star){
      this._init(star);
    }
    var _vote = parseInt(vote);
    this._current = _vote;
    this.block.setAttribute('data-vote',_vote);
    if(_vote > 0){
      for(var _index = 0;_index < this.items.length;_index++){
        if(_vote > 0){
          BX.addClass(this.items[_index],this.activeClass);
          _vote--;
        }
        else{
          BX.removeClass(this.items[_index],this.activeClass);
        }
      }
    }
  },
  EventHover : function(star){
    var rating = this._init(star);
    if(BX.hasClass(star,this.itemClass)){
      var i = 1;
      while(star.previousSibling){
          star = star.previousSibling;
          if(star.nodeType === 1){
              i++;
          }
      }
      this.SetVote(i,rating.block);
    }
  },
  Send : function(star){
    if(BX.hasClass(star,this.buttonClass)){
      star = BX.findParent(star,{'class':'rating'},true);
    }
    var rating = this._init(star);
    if(BX.hasClass(star,this.itemClass)){
      var i = 1;
      while(star.previousSibling){
          star = star.previousSibling;
          if(star.nodeType === 1){
              i++;
          }
      }
      this.SetVote(i,rating.block);
    }

    if(BX.hasClass(star,'rating')){
      var i = rating._current;
    }
      var _form = BX.findChild(rating.block,{'tag':'form'},true), url = window.location.protocol + '//' + window.location.hostname + _form.getAttribute('action');
      var data = {'object': rating.block.getAttribute('data-object'),
                  'vote' : i};
      BX.ajax({url: url,
              data: data,
              method: 'POST',
              dataType: 'json',
              timeout: 30,
              async: true,
              processData: true,
              scriptsRunFirst: true,
              emulateOnload: true,
              start: true,
              cache: false,
              onsuccess: function(json){
                  if(json.error){
                      alert('Error:'+json.error);
                  }
                  if(json.result){
                    alert('Vote: Object:'+json.object+'; Vote:'+json.vote);
                  }
              },
              onfailure: function(error){
                alert('Error Vote');
              }
            });
  }
};
BX.ready(function(){
  var rating = BX.findChildren(document,
                              {'class':'rating'},
                              true);
  if(rating && rating.length){
    for(var i = 0; i < rating.length;i++){
        var _rating = rating[i];
        var  _Itrating = Itrating.init(_rating);
        for(var _index = 0;_index < _Itrating.items.length;_index++){
          _Itrating.items[_index].addEventListener("mouseover", function(event) {
            _Itrating.EventHover(event.target);
          }, false);
          _Itrating.items[_index].addEventListener("click", function(event) {
            _Itrating.Send(event.target);
          }, false);
        }
        var _button = BX.findChild(_rating, {'tag':_Itrating.buttonClass},true);
         _button.addEventListener("click", function(event) {
          _Itrating.Send(event.target);
        }, false);
    }
  }
})
