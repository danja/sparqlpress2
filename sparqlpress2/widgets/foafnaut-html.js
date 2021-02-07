var blubs=[];
var gsvgEl=null;
var BLUB_SIZE_SMALL=0.8;
var BLUB_EXP_DISTANCE_DEL=122;
var BLUB_EXP_DISTANCE=2.8;
var BLUB_EXPLOSION_DELTA=6;

var svgNS="http://www.w3.org/2000/svg";
var xlinkNS="http://www.w3.org/1999/xlink";
var anNS="http://rdf.desire.org/vocab/recommend.rdf#";
var rdfNS="http://www.w3.org/1999/02/22-rdf-syntax-ns#";
var imgNS="http://jibbering.com/2002/3/svg/#";
var DCNS="http://purl.org/dc/elements/1.1/";
var foafNS="http://xmlns.com/foaf/0.1/";
var wordnetNS="http://xmlns.com/wordnet/1.6/";
var svgrNS="http://www.w3.org/2001/svgRdf/axsvg-schema.rdf#";
var jimNS="http://jibbering.com/foaf/jim.rdf#";

var startURL="cache/";
var endURL=".xml";

function Blub(x, y, startx, starty, label, type, id, outlink, interactive) {
  if (blubs[id]) {
    return blubs[id];
  }
  this.x=x;
  this.y=y;
  this.id=id
  this.label=label;
  this.neighbours=[];
  this.outNeighbours=[];
  this.inlines=[];
  this.outlines=[];
  this.count_connections=0;
  this.link=startURL+id+endURL;
  this.outlink=outlink;
  this.startx=startx;
  this.starty=starty;
  blubs[this.id]=this;
  this.group=createBlub(this, interactive);
  if (x!=startx || y!=starty)
    setTimeout("moveBlub('" + this.id + "')", 10);
}

function moveBlubTo(blub,x,y) {
  blub.group.style.left=x+"px";
  blub.group.style.top=y+"px";
  blub.groupx=x;
  blub.groupy=y;
  if (document.body.addBehavior) {
    var bi=blub.inlines;
    var bil=bi.length;
    for (var i=0; i<bil; i++) {
      var bili=bi[i];
      try {
        bili.from=pos(blub);
      } catch (e) {}
    }
    var bi=blub.outlines;
    var bil=bi.length;
    for (var i=0; i<bil; i++) {
      var bili=bi[i];
      try {
        bili.to=pos(blub);
      } catch (e) {}
    }
  } else {
    if (window.SVGElement) {
      var bi=blub.inlines;
      var bil=bi.length;
      for (var i=0; i<bil; i++) {
        var bili=bi[i];
        try {
          bili.setAttribute('x1',svgposX(blub));
          bili.setAttribute('y1',svgposY(blub));
        } catch (e) {}
      }
      var bi=blub.outlines;
      var bil=bi.length;
      for (var i=0; i<bil; i++) {
        var bili=bi[i];
        try {
          bili.setAttribute('x2',svgposX(blub));
          bili.setAttribute('y2',svgposY(blub));
        } catch (e) {}
      }
    }
  }
}

function selectBlub(id) {
  var blub=blubs[id];
  var label=document.getElementById('label');
  label.innerHTML="<a href='"+blub.outlink+"'>"+blub.label+"</a>";
}

function deleteBlub(id) {
  var blub=blubs[id];
  blub.group.parentNode.removeChild(blub.group);
  var bi=blub.inlines;
  var bil=bi.length;
  for (var i=0; i<bil; i++) {
    var bili=bi[i];
    bili.parentNode.removeChild(bili);
  }
  var bi=blub.outlines;
  var bil=bi.length;
  for (var i=0; i<bil; i++) {
    var bili=bi[i];
    bili.parentNode.removeChild(bili);
  }
  blub.deleted=1;
}

function moveBlub(id) {

  // The recursively called moveBlub function
  // creates the explosions.
  // BLUB_EXPLOSION_DELTA effects the number of steps it takes.
  
  var theBlub=blubs[id];
  nowX=theBlub.group.offsetLeft;
  nowY=theBlub.group.offsetTop;
  var toX=theBlub.x;
  var toY=theBlub.y;
  var dx=toX-nowX;
  var dy=toY-nowY;
  
  if (dx > -10 && dx < 10 && dy > -10 && dy < 10) {
    var nowToX=toX;
    var nowToY=toY;
  } else {
    var nowToX=nowX + dx/BLUB_EXPLOSION_DELTA;
    var nowToY=nowY + dy/BLUB_EXPLOSION_DELTA;
    setTimeout("moveBlub('" + id + "')", 10);
  }
  moveBlubTo(theBlub, nowToX, nowToY);
}
function createBlub(obj, interactive) {
  if (interactive) {
    var str="<div class=head onclick='getMoreInfo(\""+obj.id+"\")'>+</div><div class=bodyTop></div><div class=apron onmousedown='startDrag(event)'>"+obj.label+"</div><div class=bodyBottom></div><div class=feet onclick='deleteBlub(\""+obj.id+"\")'>-</div>";
  } else {
    var str="<div class=head>+</div><div class=bodyTop></div><div class=apron>"+obj.label+"</div><div class=bodyBottom></div><div class=feet>-</div>";
  }
  var div=document.createElement('div');
  div.className="blub";
  div.innerHTML=str;
  div.id=obj.id;
  var container=document.getElementById('blubContainer');
  container.appendChild(div);
  var apron=div.childNodes.item(2);
  if (obj.startx > 0 && obj.starty > 0) {
    div.style.left=obj.startx+"px";
    div.style.top=obj.starty+"px";
  }
  return div;
}
function HTTP() {
  var xmlhttp;
  if (typeof XMLHttpRequest != 'undefined') {
    xmlhttp=new XMLHttpRequest();
  } else {
    if (typeof ActiveXObject !='undefined') {
      try {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
      } catch (e) {}
    }
  }
  return xmlhttp;
}

function getMoreInfo(id) {
  selectBlub(id);
  var blub=blubs[id];
  blub.group.firstChild.innerHTML='.';
  blub.group.firstChild.onclick=function() {}
  blub.group.firstChild.onclick=new Function("selectBlub('"+id+"')");
  getPerson(blub.link,id);
}
function getPerson(link,id) {
  var xmlhttp=new HTTP();
  xmlhttp.open("GET",link,true);
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4) {
      try {
        var res = eval('(' + xmlhttp.responseText + ')');
      } catch(e) {
        document.getElementById(id).title='Error parsing results';
      }
        var tmpList=[];
        var dirList=[];
        var count=0;
        var blub=blubs[id];
        blub.group.firstChild.innerHTML='o';
        for (var i=0;i<res.results.bindings.length;i++) {
          var d=res.results.bindings[i];
          var key=d.oid.value;
          if (typeof d.o!='undefined' || d.o!=null)
            key = d.o.value;
          if (!dirList[key]) {
            if (typeof d.olink=='undefined' || d.olink==null) {
              d.olink=[];
              d.olink.value='';
            }
            if (typeof d.otype=='undefined' || d.otype==null) {
              d.otype=[];
              d.otype.value='';
            }
            dirList[count]={id:d.oid.value,link:startURL+d.oid.value+endURL,type:d.ptype.value,label:d.olabel.value,blubType:d.otype.value,outlink:d.olink.value};
            dirList[key]=1;
            count++;
          }
        }
        blub['count_connections']=dirList.length;;
        addDirs(blub.id,dirList);
      selectBlub(id);
    }
  }
  xmlhttp.send(null);
}

function addDirs(id,dirList) { 
  var theBlub=blubs[id];
  var numBlubsToCreate=dirList.length;
  for (var i=0; i<dirList.length; i++) {
    var dir=dirList[i];
    var existing=blubs[dir.id];
    if (typeof existing=='undefined' || existing == null) {
      newX=theBlub.group.offsetLeft + Math.round(1.01*Math.cos(2*Math.PI/numBlubsToCreate*(i%numBlubsToCreate)) * (numBlubsToCreate*BLUB_EXP_DISTANCE+BLUB_EXP_DISTANCE_DEL) );
      newY=theBlub.group.offsetTop + Math.round(1.0*Math.sin(2*Math.PI/numBlubsToCreate*(i%numBlubsToCreate)) * (numBlubsToCreate*BLUB_EXP_DISTANCE+BLUB_EXP_DISTANCE_DEL) );
      var blub=new Blub(newX,newY,theBlub.group.offsetLeft,theBlub.group.offsetTop,dir.label,dir.blubType,dir.id,dir.outlink,true);
      theBlub.neighbours.push(blub);
      theBlub.neighbours[blub.id]=theBlub.neighbours[theBlub.neighbours.length-1];
      blub.neighbours.push(theBlub);
      blub.neighbours[theBlub.id]=blub.neighbours[blub.neighbours.length-1];
      addLine(theBlub,blub,dir.type,false);
    } else {
      if (existing.deleted != 1) {
        // Add the lines to an existing blub.
        theBlub.neighbours.push(existing);
        theBlub.neighbours[existing.id]=theBlub.neighbours[theBlub.neighbours.length-1];
        existing.neighbours.push(theBlub);
        existing.neighbours[theBlub.id]=existing.neighbours[existing.neighbours.length-1];
        addLine(theBlub, existing,dir.type,true);
      } else {
        
        // This is where you would bring a deleted blub back to life,
        // then add the existing lines etc.
   
      }
    }
  }  
  theBlub.expanded=1;
}

function addLine(from, to,type,existing) {
  if (document.body.addBehavior) {
    var shp=document.createElement('v:line');
    shp.style.position="absolute";
    shp.style.height="5000px";
    shp.style.width="5000px";
    shp.strokecolor="#ff0000";
    shp.opacity=0.5;
    shp.strokeweight="2px";
    shp.from=pos(from);
    shp.to=pos(to);
    document.body.appendChild(shp);
    from.outlines.push(shp);
    to.inlines.push(shp);
    from.outNeighbours.push(to);
  }
  if (window.SVGElement) {
    var shp=document.createElementNS(svgNS,'line');
    shp.setAttributeNS(null,"stroke","#ff0000");
    shp.setAttributeNS(null,"stroke-width","2px");
    shp.setAttributeNS(null,'x1',svgposX(from)+"px");
    shp.setAttributeNS(null,'y1',svgposY(from)+"px");
    shp.setAttributeNS(null,'x2',svgposX(to)+"px");
    shp.setAttributeNS(null,'y2',svgposY(to)+"px");
    gsvgEl.appendChild(shp);
    from.outlines.push(shp);
    to.inlines.push(shp);
    from.outNeighbours.push(to);

  }
}
 function pos(grp) {
   var gg=grp.group;
   return (gg.offsetLeft+Math.floor(gg.offsetWidth/2)-12)+","+(gg.offsetTop+Math.floor(gg.offsetHeight/2)-12);
 }

 function svgposX(grp) {
   var gg=grp.group;
   return (gg.offsetLeft+Math.floor(gg.offsetWidth/2)-12);
 }

 function svgposY(grp) {
   var gg=grp.group;
   return (gg.offsetTop+Math.floor(gg.offsetHeight/2)-12);
 }

function getDragParent(el) {
  var oldEl=el;
  while (el) {
    el=el.parentNode;
    if (el.className=="blub" || el.nodeName.toUpperCase()=='BODY') {
      return el;
    }
    oldEl=el;
  }
}

var offsetX,offsetY,draggingThing;
function startDrag(e) {
  draggingThing=getDragParent(e.srcElement || e.target); 
  offsetX=e.clientX-draggingThing.offsetLeft;
  offsetY=e.clientY-draggingThing.offsetTop;
  draggingThing=blubs[draggingThing.id];
  draggingThing.group.onmousemove=moveDrag;
  draggingThing.group.onmouseup=endDrag;
  document.onselectionchange=clearSel;
  document.body.onmousemove=moveDrag;
  document.body.onmouseup=endDrag;
  return false;
}
function clearSel() {
  document.selection.clear();
}

function nullFunc(e) {
  return false;
}
function moveDrag(e) {
  e=e || event;
  if (draggingThing) {
    moveBlubTo(draggingThing,e.clientX-offsetX,e.clientY-offsetY);
    return true;
  }
}
function endDrag(e) {
  draggingThing.group.onmousemove=null;
  draggingThing.group.onmouseup=null;
  draggingThing.group.onselectstart=null;
  draggingThing=null;
  document.body.onmousemove=null;
  document.body.onmouseup=null;
  document.onselectstart=null;
}

function start(sURL, eURL) {
  startURL=sURL;
  endURL=eURL;
  if (document.createElementNS) {
    gsvgEl=document.createElementNS(svgNS,'svg');
    gsvgEl.setAttributeNS(null,'height',"5000px");
    gsvgEl.setAttributeNS(null,'width',"5000px");
    document.getElementById('blubContainer').parentNode.appendChild(gsvgEl);
  }
  document.onselectstart=nullFunc;
}
