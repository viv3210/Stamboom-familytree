/* Versie 0.83.02152048						*/

function drawstamboom () {
	/* START ---- Variables to be defined 	*/
	var trackProband 			= 0;			// Zet op 1 als je de cookies en kleuren wil om je weg terug te vinden
	
	var IndividuColor 			= "#ff6633";	// Kleur van geselecteerde persoon 					Pick a color here: http://www.w3schools.com/colors/colors_picker.asp
	var ProbandColor			= "#ffb366";	// Proband in dit kleur, als het NIET de geselecteerde persoon is. Maw: als de proband op de pagina staat, maar niet geselecteerd is
	var ProbandSelectedColor	= "#cc6600";	// Proband in dit kleur als het de geselecteerde persoon is
	var ProbandGuide			= "#ff8000";	// Via deze persoon kun je terug bij de proband geraken
	var TextColor 				= "#000000";
	var RectColor 				= "#ffcc66";	// Alle andere personen behalve geselecteerde persoon, proband, en probandguide
	var RectColorPrivacy 		= "#ffaa66";
	var CanvasColor 			= "#ccccff";
	var maxNameLength = 24; 	//When longer than 25 characters, we need to put the name on two lines
	var textOffset = 15; 		//# of px names have to start from the left border
	var yOffset = 4;
	var CanvasLeftMargin = 50;
	var CanvasRightMargin = 50;
	var TextFont = "16px serif";
	var DateFont = "14px serif";

	var CanvasTopMargin = 20;
	var CanvasBottomMargin = 20;

	var KidsVSpace = 20; //Vertical space between squares of kids
	var RectLength = 200;
	var RectHeight = 80;

	var xDist = 60;		//Distance between coloms. I.e. between kids and parent
	var yInLaw = 80; //y distance between mother of father, and father of mother
	var yParents = 40; //y distance between father of father and mother of father
	var Ix = 260;	// x-coordinate of I. Always the same, also when there are no kids
	/* NEW Om infobox standaard te tonen */
	var yInfoboxHeader = 60;
	var yInfoboxToFather = 15;
	var yInfoboxTopMargin = 20;
	var yInfobox = yInfoboxHeader + 5; 	// Height of infobox. 144 for 3 lines. Includes header
	var xInfobox = RectLength + xDist + RectLength;
	var InfoboxHeaderColor = "#443d69";
	var InfoboxHeaderFont = "bold 24px serif";
	var InfoboxHeaderFontColor = "#ffffff";
	var InfoboxBodyColor = "#fefefe";
	var InfoboxBodyFont = "16px serif";
	var InfoboxBodyFontBold = "bold 16px serif";
	var InfoboxBodyFontColor = "#000000";
	var InfoboxBodyLineDistance = 25;
	yInfobox += 26*infoLines;
	/* END NEW */
	/* END ----- Variables to be defined */
	
	/* Check proband en construeer pad naar proband */
	if (trackProband) {
		setProband(individuNummer);
		var probandGuidesInit =	getCookie("guides");
		var probandInit = 		getCookie("proband");
	}
	/* End check proband etc. */
	

	var rectangles = [];
	var rectanglesID = [];
	var amax1 = yInfoboxTopMargin + yInfobox + yInfoboxToFather + RectHeight + yParents + RectHeight + yInLaw/2;
	var amax2 = CanvasTopMargin + (Kids.length * RectHeight)/2+((Kids.length) - 1) * KidsVSpace/2;
	var a = Math.max(amax1, amax2); // Midden van y coordinaat voor Individu

	CanvasMinHeight1 = a - yInfoboxTopMargin + yInLaw/2 + RectHeight + yParents + RectHeight;
	CanvasMinHeight2 = a - CanvasTopMargin + (Kids.length * RectHeight)/2 + ((Kids.length) - 1) * KidsVSpace/2;
	CanvasMinHeight = Math.max(CanvasMinHeight1, CanvasMinHeight2);

	var Ix = CanvasLeftMargin + RectLength + xDist;	// x-coordinate of I. Always the same, also when there are no kids
	var px = Ix + RectLength + xDist;			// x-coordinate of parents
	var ppx = px + RectLength + xDist;

	var CanvasMinWidth = (4 * RectLength) + (3 * xDist);
	var CanvasHeight =  CanvasTopMargin + CanvasBottomMargin + CanvasMinHeight;
	var CanvasWidth =  CanvasLeftMargin + CanvasRightMargin + CanvasMinWidth;

	var ModalWidth = 0.5 * CanvasWidth;

	//document.title = "Stamboom van " + I2;
	document.write("<div><canvas id=\"myCanvas\" width=\"" + CanvasWidth + "\" height=\""+ CanvasHeight + "\" style=\"float:left;margin-right:20px;margin-bottom:15px\">Your browser does not support the &lt;canvas&gt; element.</canvas></div>");

	var CanvasYMid = (CanvasMinHeight/2);

	// Drawing canvas.
	var cv=document.getElementById("myCanvas");
	var canvOK=1;
	try {cv.getContext("2d");}
	catch (er) {canvOK=0;}
	if (canvOK==1) {
		var ay = ycor(a);					 	// y coor linker bovenhoek persoon

		var b = a - (yInLaw/2) - (RectHeight/2);//mother of father
		var by = ycor(b);
		
		var c = b - yParents - RectHeight; // father of father 
		var cy = ycor(c);
		
		var d = (b + c)/2; //father
		var dy = ycor(d);
		
		var e = a + (yInLaw/2) + (RectHeight/2);//father of mother
		var ey = ycor(e);
		
		var f = e + yParents + RectHeight; //mother of mother
		var fy = ycor(f);
		
		var g = (e + f)/2; //mother
		var gy = ycor(g);
		
		var h = a - yParents - RectHeight;
		var hy = ycor(h);
		
		var ctx=cv.getContext("2d");
		ctx.font=TextFont;
		ctx.fillStyle=CanvasColor;
		ctx.fillRect(0,0,CanvasWidth,CanvasHeight);
		ctx.fillStyle=RectColor;
		
		//Draw rectangle to show margins DEBUG
		/* ctx.strokeRect(CanvasLeftMargin, CanvasTopMargin, CanvasMinWidth, CanvasMinHeight);
		drawLine(0,a,CanvasWidth,a);*/

		// If there are kids, put them on the grid
		if (Kids.length > 0) {
			yKid1 = a - (Kids.length*RectHeight + (Kids.length - 1)*KidsVSpace)/2;
			yLineFirstKid = yKid1 + (RectHeight/2);
			//yLineFirstKid = CanvasTopMargin + RectHeight/2; 	// Heighest point of vertical line between kids and parents
			yLineLastKid = yLineFirstKid;
			y = yKid1 - RectHeight - KidsVSpace;
			for (i=0;i<Kids.length;i++) {
				y += RectHeight + KidsVSpace;
				//drawRect(CanvasLeftMargin, y); rectanglesID.push(KidsID[i]);
				drawRect(CanvasLeftMargin, y, KidsID[i], ((KidsColour[i] == "1")?"mark":"normal"));	// Draws rectangle around kids
				yLineLastKid = y + RectHeight/2;
				drawLine(CanvasLeftMargin + RectLength,yLineLastKid,CanvasLeftMargin + RectLength + xDist/2,yLineLastKid);
				ctx.fillStyle=TextColor;
				writeName(Kids[i], CanvasLeftMargin, yLineLastKid, KidsBD[i]);

				if (KidsK[i] > 0) {
					// the triangle
					ctx.beginPath();
					ctx.moveTo(CanvasLeftMargin-1, yLineLastKid-5);
					ctx.lineTo(CanvasLeftMargin-10, yLineLastKid);
					ctx.lineTo(CanvasLeftMargin-1, yLineLastKid+5);
					ctx.lineTo(CanvasLeftMargin-1, yLineLastKid-5);
					ctx.closePath();
					 
					// the outline
					ctx.lineWidth = 1;
					ctx.strokeStyle = '#000000';
					ctx.stroke();

					switch (KidsK[i]) {
						case "1":
							ctx.fillStyle = "#ffffff";
							break;
						case "2":
							ctx.fillStyle = "#bfbfbf";
							ctx.fillStyle = "#999999";
							break;
						case "3":
							ctx.fillStyle = "#000000";
							break;
						default:
							ctx.fillStyle = CanvasColor;
					}

					// the fill color
					ctx.fill();
					ctx.fillStyle=RectColor;
				}
			}
			drawLine(CanvasLeftMargin + RectLength + xDist/2, yLineFirstKid, CanvasLeftMargin + RectLength + xDist/2, yLineLastKid);
			drawLine(CanvasLeftMargin + RectLength + xDist/2,a,Ix,a);
		}

		// If the parents are known, put them on the grid
		drawRect(Ix, ay, "I0", "individu"); ctx.fillStyle=RectColor;
		if (ParentsID[0] != "I0") {drawRect(px, dy, ParentsID[0], (ParentColour[0] == "1"?"mark":"normal"));}
		if (ParentsID[3] != "I0") {drawRect(px, gy, ParentsID[3], (ParentColour[3] == "1"?"mark":"normal"));}	
		if (ParentsID[1] != "I0") {drawRect(ppx, cy, ParentsID[1], (ParentColour[1] == "1"?"mark":"normal"));} 
		if (ParentsID[2] != "I0") {drawRect(ppx, by, ParentsID[2], (ParentColour[2] == "1"?"mark":"normal"));}
		if (ParentsID[4] != "I0") {drawRect(ppx, ey, ParentsID[4], (ParentColour[4] == "1"?"mark":"normal"));} 
		if (ParentsID[5] != "I0") {drawRect(ppx, fy, ParentsID[5], (ParentColour[5] == "1"?"mark":"normal"));}	
		
		writeName(I,Ix, a, GSData); writeName(Parents[0], px, d, PBD[0]); writeName(Parents[1], ppx, c, PBD[1]); writeName(Parents[2], ppx, b, PBD[2]); writeName(Parents[3], px, g, PBD[3]); writeName(Parents[4], ppx, e, PBD[4]); writeName(Parents[5], ppx, f, PBD[5]);
		/* NEW Draw Infobox on canvas*/
		ctx.fillStyle = InfoboxHeaderColor;
		var x1 = Ix;										// hor position (same hight as main person)
		var y1 = yInfoboxTopMargin;			// vert position; cy = y coord of upper corner father's father
		var yhh = yInfoboxHeader;
		ctx.font = InfoboxHeaderFont;
		info.name += (info.alias?" ("+info.alias+")":"");
		InfoboxWidthName = ctx.measureText(info.name).width; 	//Mininum width: default unless name or other data makes it longer
		// Calculate other widths
		InfoboxWidthBirth = 0; InfoboxWidthDeath = 0; InfoboxWidthOccupation = 0;
		// InfoboxWidthOccupation = ctx.measureText(info.occupation).width;		** Not needed as defined further? **
		
		pob = info.pob.replace(", Belgium", "");		// Remove Belgium in place of birth
		pob = pob.replace(", West-Vlaanderen", "");		// Remove West-Vlaanderen in place of birth
		var separatorBirth = "";
		var separatorDeath = "";
		if (info.dob !="") {separatorBirth = ", ";}
		if (info.dod !="") {separatorDeath = ", ";}
		pob = (pob != ""?separatorBirth+pob:"");					// If place of birth exists, prefix it with ", "
		pod = info.pod.replace(", Belgium", "");		// Remove Belgium in place of death
		pod = pod.replace(", West-Vlaanderen", "");		// Remove West-Vlaanderen in place of death
		pod = (pod != ""?separatorDeath+pod:"");					// If place of death exists, prefix it with ", "
		if (info.dod + pod !="") {pod += (info.age != ""?" ("+info.age+")":"");}
		else {pob += (info.age != ""?" ("+info.age+")":"");}
		
		if (info.dob !== "" || info.pob !== "") {
			ctx.font = InfoboxBodyFontBold;
			InfoboxWidthBirth = ctx.measureText("Geboren: ").width;
			ctx.font = InfoboxBodyFont;
			InfoboxWidthBirth += ctx.measureText(info.dob + pob).width;
		}
		if (info.dod !== "" || info.pod !== "") {
			ctx.font = InfoboxBodyFontBold;
			InfoboxWidthDeath = ctx.measureText("Overleden: ").width;
			ctx.font = InfoboxBodyFont;
			InfoboxWidthDeath += ctx.measureText(info.dod + pod).width;
		}
		if (info.occupation != "") {
			ctx.font = InfoboxBodyFontBold;
			InfoboxWidthOccupationLabel = ctx.measureText("Beroep: ").width;
			ctx.font = InfoboxBodyFont;
			InfoboxWidthOccupation = InfoboxWidthOccupationLabel + ctx.measureText(info.occupation).width; 	// Max: 750
			if (InfoboxWidthOccupation > 770) {
				x1 -= (InfoboxWidthOccupation - 690);
			}
		}
			
		
		minInfoboxWidth = Math.max(InfoboxWidthName + 2*textOffset, InfoboxWidthBirth + 2*textOffset, InfoboxWidthDeath + 2*textOffset, InfoboxWidthOccupation + 2*textOffset, xInfobox);
				
		ctx.strokeRect(x1, y1, minInfoboxWidth, yInfobox);
		ctx.fillRect(x1, y1, minInfoboxWidth, yhh);
		ctx.fillStyle = InfoboxBodyColor;
		ctx.fillRect(x1, y1 + yInfoboxHeader, minInfoboxWidth, yInfobox - yhh);
		ctx.font = InfoboxHeaderFont;
		ctx.fillStyle = InfoboxHeaderFontColor;
		ctx.fillText(info.name, x1+15, y1+yInfoboxHeader/2+6);

		ctx.font = InfoboxBodyFontBold;
		ctx.fillStyle = InfoboxBodyFontColor;
		var yPos = y1+yInfoboxHeader+20;
		if (info.dob !== "" || info.pob !== "") {ctx.fillText("Geboren:", x1+15, yPos); yPos += InfoboxBodyLineDistance;}
		if (info.dod !== "" || info.pod !== "") {ctx.fillText("Overleden:", x1+15, yPos); yPos += InfoboxBodyLineDistance;}
		if (info.occupation != "") {ctx.fillText("Beroep:", x1+15, yPos); yPos += InfoboxBodyLineDistance;}
		if (PartnerMarriages == 1) {
			if (PartnerMarriageDate[0] || PartnerMarriagePlace[0]) {ctx.fillText("Huwelijk:", x1+15, yPos); yPos += InfoboxBodyLineDistance;}
		} 
		if (PartnerMarriages > 1) {
			for ($marriage = 1; $marriage <= PartnerMarriages; $marriage++) {
				$tmpTxt = "Huwelijk "+$marriage+": ";
				if (PartnerMarriageDate[$marriage-1] || PartnerMarriagePlace[$marriage-1]) {ctx.fillText($tmpTxt, x1+15, yPos); yPos += InfoboxBodyLineDistance;}
			}
		}
		//ctx.fillText("Meter:", x1+15, y1+yInfoboxHeader+95);

		ctx.font = InfoboxBodyFont;
		yPos = y1+yInfoboxHeader+20;
		
		if (info.dob + pob !== "") {ctx.fillText(info.dob + pob, x1+85, yPos); yPos += InfoboxBodyLineDistance;}
		if (info.dod + pod !== "") {ctx.fillText(info.dod + pod, x1+97, yPos); yPos += InfoboxBodyLineDistance;}
		if (info.occupation !== "") {ctx.fillText(info.occupation, x1+75, yPos); yPos += InfoboxBodyLineDistance;}
		if (PartnerMarriages != 0) {
			for (pm = 0; pm < PartnerMarriages; pm++) {
				PartnerMarriagePlace[pm] = PartnerMarriagePlace[pm].replace(", Belgium", "");
				PartnerMarriagePlace[pm] = PartnerMarriagePlace[pm].replace(", West-Vlaanderen", "");
				tmpTxt = "";
				if (PartnerMarriages >1) {tmpTxt += "(" + Partner[pm] + ") ";}
				tmpTxt += PartnerMarriageDate[pm]
				if (PartnerMarriagePlace[pm] != "") {tmpTxt += " ("+PartnerMarriagePlace[pm]+")";}
				//ctx.fillText(tmpTxt, x1+90+fn_extraSpace(PartnerMarriages)*10, y1+yInfoboxHeader+70+25*(pm+1));
				if (PartnerMarriageDate[pm] || PartnerMarriagePlace[pm]) {
					ctx.fillText(tmpTxt, x1+90+fn_extraSpace(PartnerMarriages)*10, yPos);
					yPos += InfoboxBodyLineDistance;
				}
			// yPos += InfoboxBodyLineDistance;			// <= moved inside previous (if) clause for those cases where we don't know the date or place of a previous marriage. E.g. Hélène, ID=524
			}
		}
		// drawLine(0, yInfoboxTopMargin + yInfobox + yInfoboxToFather, CanvasWidth, yInfoboxTopMargin + yInfobox + yInfoboxToFather);		// Check where father of father should be 	DEBUG
		/* END Draw Infobox */

		ctx.font = TextFont; // To write text in kids and parents 
		// The partner, as stored in Partner[0], [1] and PartnerID[0], ...
		var a_par = Partner.length;
		for (pp = 0; pp < a_par; pp++) { //Can't get here if you don't have a partner
			p_name = Partner[pp];
			//drawRect(Ix, hy, PartnerID[pp], "normal");	// <= This line would always the partner as if no privacy. Next line solves that
			drawRect(Ix, hy, PartnerID[pp], (PartnerColour == "1"?"mark":"normal"));
			writeName(Partner[pp], Ix, h, PartnerBD[pp]);
			yto1 = a - RectHeight/2;
			yto2 = a + RectHeight/2;
			yto =  (Math.abs(h - yto1) < Math.abs(h - yto2))?yto1:yto2;
			yfrom = (Math.abs(h - yto1) > Math.abs(h - yto2))?h - RectHeight/2:h + RectHeight/2;
			drawLine(Ix + RectLength/2,yfrom,Ix + RectLength/2,yto)
			h = h + 2 * yParents + 2 * RectHeight;
			hy = hy + 2 * yParents + 2 * RectHeight;
		}
		
		// Connecting the dots... or adding lines
			// Vertical lines
				if (ParentsID[0] != "I0") {drawLine((Ix+RectLength+px)/2,d,(Ix+RectLength+px)/2,(d+g)/2);}	// between person and father
				if (ParentsID[3] != "I0") {drawLine((Ix+RectLength+px)/2,(d+g)/2,(Ix+RectLength+px)/2,g);}	// between person and mother
				
				if (ParentsID[1] != "I0") {drawLine((px+RectLength+ppx)/2,c,(px+RectLength+ppx)/2,(c+b)/2);} // between parents and their parents (father)
				if (ParentsID[2] != "I0") {drawLine((px+RectLength+ppx)/2,(c+b)/2,(px+RectLength+ppx)/2,b);} // between parents and their parents (father)
				
				if (ParentsID[4] != "I0") {drawLine((px+RectLength+ppx)/2,e,(px+RectLength+ppx)/2,(e+f)/2);}	// between parents and their parents (mother)
				if (ParentsID[5] != "I0") {drawLine((px+RectLength+ppx)/2,(e+f)/2,(px+RectLength+ppx)/2,f);}	// between parents and their parents (mother)
			// Horizontal lines
				//Left side of lines
					if (ParentsID[0] != "I0" || ParentsID[3] != "I0") {drawLine((Ix+RectLength),a,(Ix+RectLength+px)/2,a);}		// Person to parents
					if (ParentsID[1] != "I0" || ParentsID[2] != "I0") {drawLine((px+RectLength),d,(px+RectLength+ppx)/2,d);}	//Father to parents
					if (ParentsID[4] != "I0" || ParentsID[5] != "I0") {drawLine((px+RectLength),g,(px+RectLength+ppx)/2,g);}	//Mother to parents
				//Right side of lines
					// Person to parents
					if (ParentsID[0] != "I0") {drawLine((Ix + RectLength + px)/2,d,px,d);}
					if (ParentsID[3] != "I0") {drawLine((Ix + RectLength + px)/2,g,px,g);}
					// parents to grandparents
					if (ParentsID[1] != "I0") {drawLine((px + RectLength + ppx)/2,c,ppx,c);}
					if (ParentsID[2] != "I0") {drawLine((px + RectLength + ppx)/2,b,ppx,b);}
					if (ParentsID[4] != "I0") {drawLine((px + RectLength + ppx)/2,e,ppx,e);}
					if (ParentsID[5] != "I0") {drawLine((px + RectLength + ppx)/2,f,ppx,f);}

		function drawLine(fromx,fromy,tox,toy){
			ctx.beginPath();
			ctx.moveTo(fromx,fromy);
			ctx.lineTo(tox,toy);
			ctx.stroke();
			ctx.closePath();
		}
		
		function ycor(ymid) {return ymid - (RectHeight/2);}
		
		function checkLength(myName) {	// Input: full name (e.g. Gaston Emile Louis Henri Vanbiervliet). Output: array with usable names, e.g. ["Gaston Emile Louis", "Vanbiervliet"]
			tl=myName.length;
			if (tl > maxNameLength) {
				// Try if splitting the last name is enough
				var n = myName.lastIndexOf(" ", maxNameLength);
				myName2 = myName.substring(n);
				var r = myName.substring(0,n)+","+myName2;
				if (myName2.length > maxNameLength) { //one more time
					var m = myName2.lastIndexOf(" ", maxNameLength);
					var r = myName.substring(0,n)+","+myName2.substring(0,m)+","+myName2.substring(m+1);
				}
			}
			else {r = myName;}
			return r;
		}
		
		function writeName(wie, waarx, waary, extra) {
			waary2 = waary + yOffset;
			tname = checkLength(wie);
			komma = (tname.match(/,/g)||[]).length;
			lijnen = komma + 1;
			lijnen += (extra?1:0);
			ctx.fillStyle=TextColor;
			switch (lijnen) {
				case 2:
					if (extra) {
						ctx.fillText(tname, waarx + textOffset, waary2 - 10);
						ctx.font=DateFont;
						ctx.fillText(extra, waarx + textOffset, waary2 + 10);
						ctx.font=TextFont;
					}
					else {
						ctx.fillText(tname.substring(0,tname.indexOf(",")), waarx + textOffset, waary2 - 10);
						ctx.fillText(tname.substring(tname.indexOf(",")+2), waarx + textOffset, waary2 + 10);
					}
					break;
				case 3:
					if (extra) {
						var res = tname.split(",");
						ctx.fillText(res[0],waarx + textOffset, waary2 - 20);
						ctx.fillText(res[1].trim(),waarx + textOffset, waary2);
						ctx.font=DateFont;
						ctx.fillText(extra,waarx + textOffset, waary2 + 20);
						ctx.font=TextFont;
					}
					else {
						var res = tname.split(",");
						ctx.fillText(res[0],waarx + textOffset, waary2 - 20);
						ctx.fillText(res[1].trim(),waarx + textOffset, waary2);
						ctx.fillText(res[2].trim(),waarx + textOffset, waary2 + 20);
					}
					break;
				case 4:
					var res = tname.split(",");
					ctx.fillText(res[0],waarx + textOffset, waary2 - 30);
					ctx.fillText(res[1].trim(),waarx + textOffset, waary2 - 10);
					ctx.fillText(res[2].trim(),waarx + textOffset, waary2 + 10);
					ctx.font=DateFont;
					ctx.fillText(extra,waarx + textOffset, waary2 + 30);
					ctx.font=TextFont;
					break;
				default:
					ctx.fillText(tname,waarx + textOffset, waary2);
			}
			ctx.fillStyle=RectColor;
		}
		
		function Rectangle(x, y, width, height) {
			this.left = x;
			this.top = y;
			this.right = x + width;
			this.bottom = y + height;
		};
		
		//var drawRect = function (x, y, width, height, fillcolor) {
		function draw(x, y, drawtype, ID2draw) {
			// Drawtype can be: individu, mark, normal
			
			// Check what colour a person must have:
			// 		ProbandGuide: 			Leads you to the Proband, even if it's a privacy sensitive individu
			//		ProbandColor: 			Colour of proband if it's NOT the selected person
			//		RectColor: 				Any person except Proband or ProbandGuide, who is NOT privacy sensitive
			//		RectColorPrivacy: 		Not a proband, not a proband guide, but privacy sensitive
			//		ProbandSelectedColor:	Proband when selected
			// 		IndividuColor:			Selected person
			// Decision tree:
			//		If drawtype = individu = proband ==> ProbandSelectedColor, else IndividuColor
			//		If privacy 
			//			& not guide => RectColorPrivacy else ProbandGuide
			//		else (=not privacy)
			//			if guide => ProbandGuide, else RectColor
			myFillstyle = "#000000";
			if (drawtype == "individu") {
				if (trackProband) {myFillstyle = (ID2draw==probandInit?ProbandSelectedColor:IndividuColor);}
				else {myFillstyle = IndividuColor;}
			} else {
				if (trackProband) {
					if (drawtype == "mark") {
						myFillstyle = (probandGuidesInit.includes(ID2draw)) ? ProbandGuide : RectColorPrivacy;
					}
					else {
						myFillstyle = (probandGuidesInit.includes(ID2draw)) ?  ProbandGuide : RectColor;
					}
				}
				else {
					if (drawtype == "mark") {myFillstyle = RectColorPrivacy;}
					else {myFillstyle = RectColor;}
				}
			}
				
			//ctx.fillStyle = ((drawtype == "mark")?RectColorPrivacy:((drawtype=="individu")?IndividuColor:RectColor));
			ctx.fillStyle = myFillstyle;
			ctx.strokeRect(x, y, RectLength, RectHeight);
			ctx.fillRect(x, y, RectLength, RectHeight);
		}
		
		function drawRect(x, y, myID, drawRectType) {
			draw(x,y, drawRectType, myID);
			var rectangle = new Rectangle(x, y, RectLength, RectHeight);
			rectangles.push(rectangle);
			rectanglesID.push(myID);
		}
		
		function fn_extraSpace(g) {
			return g==1?0:1;
		}
	}

function updateProbandGuide(newID) {
	let proband = getCookie("proband");
	if (proband == "") {return;}
	let guides = getCookie("guides");
	let extra = "";
	if (guides == "") { // We started at proband, so new individu is either child, spouse, a parent, or a greatparent
		if (newID == ParentsID[1] || newID == ParentsID[2] || newID == ParentsID[4] || newID == ParentsID[5]) { 	// New ind. is grandparent, so add parents as well
			if (newID == ParentsID[1] || newID == ParentsID[2]) {
				extra = ParentsID[0];
			}
			if (newID == ParentsID[4] || newID == ParentsID[5]) {
				extra = ParentsID[3];
			}
			guides = extra + "!" + newID + "!";		// includes new ID
			//guides = extra + "!"					// new ID should not be in there
			setCookie("guides", guides + "!", 7);
		}
		else {	// new ind is spouse, kid, or parent
			guides = newID;	// > Not needed as we don't need guide from child or parent; only proband needs to be id'd
			setCookie("guides", guides, 7);
		}
		// setCookie("guides", guides, 7);
	}
	else { 	// Possibilities: we stray further, or we come back
		let goingBack = guides.includes(newID);
		if (goingBack) {
			
		}
		else {
			guides += newID + "!";
			setCookie("guides", guides, 7);
		}
	}
}
	
function getCookie(cname) {	// See https://www.w3schools.com/js/js_cookies.asp
		let name = cname + "=";
		let decodedCookie = decodeURIComponent(document.cookie);
		let ca = decodedCookie.split(';');
		for(let i = 0; i <ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
	return "";
}

function setCookie(cname, cvalue, exdays) {
  const d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";";
}

function setProband(individu) {
	// Lees cookie proband. Indien leeg is er nog geen proband gezet, dus de huidige persoon moet gezet worden. Indien niet leeg, niets doen
	let proband = getCookie("proband");
	if (proband == "") {
		setCookie("proband", individu, 7)
	}
}

	$('#myCanvas').click(function (ee) {
		var clickedX = ee.pageX - this.offsetLeft;
		var clickedY = ee.pageY - this.offsetTop;
		
		for (var i = 0; i < rectangles.length; i++) {
			if (clickedX < rectangles[i].right && clickedX > rectangles[i].left && clickedY > rectangles[i].top && clickedY < rectangles[i].bottom) {
				if (rectanglesID[i] != "I0") {
					//add path to the proband in the cookie
					if (trackProband) {updateProbandGuide(rectanglesID[i]);}
					newurl = "?tree=" + tree + "&ID=" + rectanglesID[i];
					window.open(newurl,"_self")
				}
			}
		}
	})
}
