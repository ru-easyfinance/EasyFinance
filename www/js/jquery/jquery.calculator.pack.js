﻿/* http://keith-wood.name/calculator.html
   Calculator field entry extension for jQuery v1.2.0.
   Written by Keith Wood (kbwood{at}iinet.com.au) October 2008.
   Dual licensed under the GPL (http://dev.jquery.com/browser/trunk/jquery/GPL-LICENSE.txt) and
   MIT (http://dev.jquery.com/browser/trunk/jquery/MIT-LICENSE.txt) licenses.
   Please attribute the author if you use it. */
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('(w($){z p=\'v\';w 2b(){q.1r=L;q.1D=[];q.1n=Z;q.2c=Z;q.1o={\'63\':[\'0\',q.V,L,\'\',\'0\',\'0\'],\'64\':[\'1\',q.V,L,\'\',\'1\',\'1\'],\'65\':[\'2\',q.V,L,\'\',\'2\',\'2\'],\'66\':[\'3\',q.V,L,\'\',\'3\',\'3\'],\'67\':[\'4\',q.V,L,\'\',\'4\',\'4\'],\'68\':[\'5\',q.V,L,\'\',\'5\',\'5\'],\'69\':[\'6\',q.V,L,\'\',\'6\',\'6\'],\'6a\':[\'7\',q.V,L,\'\',\'7\',\'7\'],\'6b\':[\'8\',q.V,L,\'\',\'8\',\'8\'],\'6c\':[\'9\',q.V,L,\'\',\'9\',\'9\'],\'6d\':[\'A\',q.V,L,\'1U-V\',\'A\',\'a\'],\'6e\':[\'B\',q.V,L,\'1U-V\',\'B\',\'b\'],\'6f\':[\'C\',q.V,L,\'1U-V\',\'C\',\'c\'],\'6g\':[\'D\',q.V,L,\'1U-V\',\'D\',\'d\'],\'6h\':[\'E\',q.V,L,\'1U-V\',\'E\',\'e\'],\'6i\':[\'F\',q.V,L,\'1U-V\',\'F\',\'f\'],\'14.\':[\'.\',q.V,L,\'4a\',\'6j\',\'.\'],\'14+\':[\'+\',q.1v,q.3g,\'1J 4b\',\'6k\',\'+\'],\'14-\':[\'-\',q.1v,q.3h,\'1J 4c\',\'6l\',\'-\'],\'14*\':[\'*\',q.1v,q.3i,\'1J 6m\',\'6n\',\'*\'],\'14/\':[\'/\',q.1v,q.3j,\'1J 6o\',\'6p\',\'/\'],\'14%\':[\'%\',q.W,q.4d,\'1J 6q\',\'6r\',\'%\'],\'14=\':[\'=\',q.W,q.3k,\'1J 6s\',\'6t\',\'=\'],\'2d\':[\'4e\',q.W,q.4f,\'4e\',\'2d\',\'p\'],\'+-\':[\'±\',q.W,q.4g,\'1J 6u-6v\',\'6w\',\'#\'],\'1X\':[\'1/x\',q.W,q.4h,\'6x\',\'6y\',\'i\'],\'6z\':[\'2e\',q.W,q.4i,\'2e\',\'6A\',\'l\'],\'4j\':[\'4k\',q.W,q.4l,\'4k\',\'4j\',\'n\'],\'6B\':[\'eⁿ\',q.W,q.4m,\'4n\',\'6C\',\'E\'],\'6D\':[\'x²\',q.W,q.4o,\'6E\',\'6F\',\'@\'],\'6G\':[\'√\',q.W,q.4p,\'4q\',\'6H\',\'!\'],\'6I\':[\'x^y\',q.1v,q.4r,\'6J\',\'6K\',\'^\'],\'6L\':[\'6M\',q.W,q.4s,\'4t\',\'6N\',\'?\'],\'6O\':[\'3l\',q.W,q.4u,\'1V 3l\',\'6P\',\'s\'],\'6Q\':[\'3m\',q.W,q.4v,\'1V 3m\',\'6R\',\'o\'],\'6S\':[\'3n\',q.W,q.4w,\'1V 3n\',\'6T\',\'t\'],\'6U\':[\'3o\',q.W,q.4x,\'1V 3o\',\'6V\',\'S\'],\'6W\':[\'3p\',q.W,q.4y,\'1V 3p\',\'6X\',\'O\'],\'6Y\':[\'3q\',q.W,q.4z,\'1V 3q\',\'6Z\',\'T\'],\'4A\':[\'#70\',q.W,q.4B,\'15 1K-2f\',\'71\',\'x\'],\'4C\':[\'#72\',q.W,q.4D,\'15 1K-73\',\'74\',\'r\'],\'4E\':[\'#75\',q.W,q.4F,\'15 1K-76\',\'77\',\'m\'],\'M+\':[\'#78\',q.W,q.4G,\'15 1K-4b\',\'79\',\'>\'],\'M-\':[\'#7a\',q.W,q.4H,\'15 1K-4c\',\'7b\',\'<\'],\'7c\':[\'#4I\',q.1e,q.4J,\'1d 4I\',\'7d\',\'B\'],\'7e\':[\'#4K\',q.1e,q.4L,\'1d 4K\',\'7f\',\'C\'],\'7g\':[\'#4M\',q.1e,q.4N,\'1d 4M\',\'7h\',\'D\'],\'7i\':[\'#4O\',q.1e,q.4P,\'1d 4O\',\'7j\',\'H\'],\'7k\':[\'#2x\',q.1e,q.4Q,\'2y 2x\',\'7l\',\'G\'],\'7m\':[\'#2z\',q.1e,q.4R,\'2y 2z\',\'7n\',\'R\'],\'4S\':[\'#7o\',q.1e,q.4T,\'7p\',\'7q\',8,\'7r\'],\'4U\':[\'#7s\',q.1e,q.4V,\'2f-7t\',\'7u\',36,\'7v\'],\'4W\':[\'#2f\',q.1e,q.4X,\'2f\',\'7w\',35,\'7x\'],\'@X\':[\'#4Y\',q.1e,q.3r,\'4Y\',\'7y\',27,\'7z\'],\'@U\':[\'#4Z\',q.1e,q.50,\'4Z\',\'7A\',13,\'7B\'],\'@E\':[\'#51\',q.1e,q.3s,\'51\',\'7C\',46,\'7D\'],\'  \':[\'\',q.1W,L,\'1W\',\'7E\'],\'14 \':[\'\',q.1W,L,\'7F-1W\',\'7G\'],\'??\':[\'??\',q.W,q.1E]};q.2A={};q.2B={};2g(z a 3t q.1o){I(q.1o[a][4]){q[q.1o[a][4]]=a}I(q.1o[a][5]){I(2h q.1o[a][5]==\'3u\'){q.2A[q.1o[a][5]]=a}19{q.2B[q.1o[a][5]]=a}}}q.3v=[];q.3v[\'\']={1Y:\'.\',52:\'...\',53:\'7H 1s v\',7I:\'54\',7J:\'54 1s v\',7K:\'55\',7L:\'55 1s 7M 2C\',7N:\'2D\',7O:\'2D 1s 2C 3w 1s 7P\',7Q:\'4S\',7R:\'2D 1s 3x V\',7S:\'4U\',7T:\'2D 1s 3x 3u\',7U:\'4W\',7V:\'7W 1s v\',7X:\'4A\',7Y:\'56 1s 15\',7Z:\'4C\',80:\'57 1s 2C 3w 15\',81:\'4E\',82:\'83 1s 2C 3t 15\',84:\'M+\',85:\'86 1L 15\',87:\'M-\',88:\'89 3w 15\',8a:\'8b\',8c:\'1Z 1L 1v\',8d:\'8e\',8f:\'1Z 1L 8g\',8h:\'8i\',8j:\'1Z 1L 4a\',8k:\'8l\',8m:\'1Z 1L 8n\',8o:\'8p\',8q:\'1Z 1L 2x\',8r:\'8s\',8t:\'1Z 1L 2z\',2i:Z};q.2E={3y:\'1w\',58:\'\',59:Z,3z:\'2F\',3A:{},3B:\'8u\',5a:\'\',5b:\'\',3C:\'\',5c:q.5d,1d:10,5e:10,2j:Z,5f:11,5g:L,5h:L};$.2G(q.2E,q.3v[\'\']);q.2H=$(\'<1a 8v="\'+q.3D+\'" 3E="3F: 5i;"></1a>\').1M(q.5j)}$.2G(2b.5k,{1F:\'8w\',V:\'d\',1v:\'b\',W:\'u\',1e:\'c\',1W:\'s\',3D:\'v-1a\',2I:\'v-8x\',3G:\'v-2J\',1G:\'v-8y\',3H:\'v-1p\',2k:\'v-8z\',3I:\'v-5l\',2K:\'v-8A\',2L:\'v-20\',2M:\'v-8B\',5d:[\'  5m\',\'5n+@X\',\'5o-@U\',\'5p*@E\',\'5q.14=14/\'],8C:[\'@X@U@E  5m\',\'8D    14 8E 5p+\',\'8F 8G 5o-\',\'8H 8I 5n*\',\'8J M+14 5q.+-14/\',\'8K  14 M-14   14%14=\'],8L:w(a){3J(q.2E,a||{});N q},8M:w(a,b,c,d,e,f,g,h){q.1o[a]=[b,(2h c==\'8N\'?(c?q.1v:q.W):c),d,e,f,g,h];I(f){q[f]=a}I(g){I(2h g==\'3u\'){q.2A[g]=a}19{q.2B[g]=a}}N q},5r:w(a,b){z c=$(a);z d=a.3K.2N()!=\'2O\';z e=(!d?L:$(\'<2O 2l="2P" 1i="\'+q.2k+\'"/>\'));z f={P:(d?e:c),1j:d,Q:(d?$(\'<1a 1i="\'+q.2I+\'"></1a>\'):q.2H)};f.1N=$.2G({},b||{});q.5s(a,f);I(d){c.2J(e).2J(f.Q).3L(\'1M.v\',w(){e.1w()});q.2m(f,\'0\',11);q.1l(f)}},5s:w(d,e){z f=$(d);I(f.21(q.1F)){N}z g=q.K(e,\'5a\');z h=q.K(e,\'2i\');I(g){f[h?\'5t\':\'5u\'](\'<1x 1i="\'+q.3G+\'">\'+g+\'</1x>\')}I(!e.1j){z i=q.K(e,\'3y\');I(i==\'1w\'||i==\'3M\'){f.1w(q.22)}I(i==\'1f\'||i==\'3M\'||i==\'5v\'){z j=q.K(e,\'52\');z k=q.K(e,\'53\');z l=q.K(e,\'58\');z m=$(q.K(e,\'59\')?$(\'<2Q/>\').2n({3N:l,8O:k,3O:k}):$(\'<1f 2l="1f" 3O="\'+k+\'"></1f>\').5w(l==\'\'?j:$(\'<2Q/>\').2n({3N:l})));f[h?\'5t\':\'5u\'](m);m.2o(q.1G).1M(w(){I($.v.1n&&$.v.2R==d){$.v.1O()}19{$.v.22(d)}N Z})}}e.P.3P(q.2S).3Q(q.2T).3R(q.2U);I(e.1j){e.Q.3P(q.2S).3Q(q.2T).3R(q.2U);e.P.1w(w(){I(!$.v.2p(f[0])){e.3S=11;$(\'.\'+$.v.3I,e.Q).2o($.v.2K)}}).5x(w(){e.3S=Z;$(\'.\'+$.v.3I,e.Q).2q($.v.2K)})}f.2o(q.1F).3L("8P.v",w(a,b,c){e.1N[b]=c}).3L("8Q.v",w(a,b){N q.K(e,b)});$.1A(d,p,e);$.1A(e.P[0],p,e)},8R:w(a){z b=$(a);I(!b.21(q.1F)){N}z c=$.1A(a,p);c.P.2r(\'3P\',q.2S).2r(\'3Q\',q.2T).2r(\'3R\',q.2U);b.23(\'.\'+q.3G).2V().1P().23(\'.\'+q.1G).2V().1P().8S(\'.\'+q.2k).2V().1P().2q(q.1F).2r(\'1w\',q.22).2r(\'1M.v\').5y();$.5z(c.P[0],p);$.5z(a,p)},8T:w(b){z c=$(b);I(!c.21(q.1F)){N}z d=b.3K.2N();I(d==\'2O\'){b.1p=Z;c.23(\'1f.\'+q.1G).24(w(){q.1p=Z}).1P().23(\'2Q.\'+q.1G).17({5A:\'1.0\',5B:\'\'})}19 I(d==\'1a\'||d==\'1x\'){c.1Q(\'.\'+q.2k+\',1f\').2n(\'1p\',\'\').1P().3T(\'.\'+q.3H).2V()}q.1D=$.5C(q.1D,w(a){N(a==b?L:a)})},8U:w(b){z c=$(b);I(!c.21(q.1F)){N}z d=b.3K.2N();I(d==\'2O\'){b.1p=11;c.23(\'1f.\'+q.1G).24(w(){q.1p=11}).1P().23(\'2Q.\'+q.1G).17({5A:\'0.5\',5B:\'8V\'})}19 I(d==\'1a\'||d==\'1x\'){z e=c.3T(\'.\'+q.2I);z f=e.3U();z g={1m:0,1k:0};e.2W().24(w(){I($(q).17(\'2X\')==\'8W\'){g=$(q).3U();N Z}});z h=q.2Y(e);c.1Q(\'.\'+q.2k+\',1f\').2n(\'1p\',\'1p\').1P().8X(\'<1a 1i="\'+q.3H+\'" 3E="1b: \'+(e.1b()+h[0])+\'25; 1y: \'+(e.1y()+h[1])+\'25; 1m: \'+(f.1m-g.1m)+\'25; 1k: \'+(f.1k-g.1k)+\'25;"></1a>\')}q.1D=$.5C(q.1D,w(a){N(a==b?L:a)});q.1D[q.1D.1R]=b},2p:w(a){N(a&&$.8Y(a,q.1D)>-1)},8Z:w(a,b,c){z d=b||{};I(2h b==\'5D\'){d={};d[b]=c}z e=$.1A(a,p);I(e){I(q.1r==e){q.1O()}3J(e.1N,d);q.1l(e)}},22:w(b){b=b.1S||b;I($.v.2p(b)||$.v.2R==b){N}z c=$.1A(b,p);$.v.1O(L,\'\');$.v.2R=b;$.v.1T=$.v.3V(b);$.v.1T[1]+=b.90;z d=Z;$(b).2W().24(w(){d|=$(q).17(\'2X\')==\'5E\';N!d});I(d&&$.1t.2s){$.v.1T[0]-=1u.1H.2Z;$.v.1T[1]-=1u.1H.30}z e={1m:$.v.1T[0],1k:$.v.1T[1]};$.v.1T=L;c.Q.17({2X:\'5F\',3F:\'91\',1k:\'-5G\',1b:($.1t.2s?\'5G\':\'92\')});$.v.2m(c,c.P.1I(),11);$.v.1l(c);e=$.v.5H(c,e,d);c.Q.17({2X:(d?\'5E\':\'5F\'),3F:\'5i\',1m:e.1m+\'25\',1k:e.1k+\'25\'});z f=$.v.K(c,\'3z\')||\'2F\';z g=$.v.K(c,\'3B\');z h=w(){$.v.1n=11;I(!c.1j&&$.1t.3W&&1c($.1t.3X,10)<7){z a=$.v.2Y(c.Q);$(\'31.\'+$.v.2M).17({1b:c.Q.1b()+a[0],1y:c.Q.1y()+a[1]})}};I($.32&&$.32[f]){c.Q.2F(f,$.v.K(c,\'3A\'),g,h)}19{c.Q[f](g,h)}I(g==\'\'){h()}I(c.P[0].2l!=\'5I\'){c.P[0].1w()}$.v.1r=c},2m:w(a,b,c){z d=q.K(a,\'1d\');z e=q.K(a,\'1Y\');b=\'\'+(b||0);b=(e!=\'.\'?b.1g(1B 26(e),\'.\'):b);a.J=(d==10?2t(b):1c(b,d))||0;a.12=q.28(a);a.1q=a.3Y=0;a.15=(c?0:a.15);a.1h=a.2u=q.1E;a.1C=11},1l:w(a){z b=q.2Y(a.Q);z c={1b:a.Q.1b()+b[0],1y:a.Q.1y()+b[1]};a.Q.5w(q.5J(a)).1Q(\'31.\'+q.2M).17({1b:c.1b,1y:c.1y});a.Q.2q().2o(q.K(a,\'5b\')+\' \'+(q.K(a,\'2i\')?\'v-93 \':\'\')+(a.1j?q.2I:\'\'));I(q.1r==a){a.P.1w()}},2Y:w(b){z c=w(a){N{94:1,95:3,96:5}[a]||a};N[1c(c(b.17(\'33-1m-1b\')),10)+1c(c(b.17(\'33-3Z-1b\')),10)+1c(b.17(\'34-1m\'),10)+1c(b.17(\'34-3Z\'),10),1c(c(b.17(\'33-1k-1b\')),10)+1c(c(b.17(\'33-5K-1b\')),10)+1c(b.17(\'34-1k\'),10)+1c(b.17(\'34-5K\'),10)]},5H:w(a,b,c){z d=a.P?q.3V(a.P[0]):L;z e=5L.97||1u.1H.98;z f=5L.99||1u.1H.9a;z g=1u.1H.2Z||1u.40.2Z;z h=1u.1H.30||1u.40.30;I(($.1t.3W&&1c($.1t.3X,10)<7)||$.1t.2s){z i=0;$(\'.v-5M\',a.Q).1Q(\'1f:3x\').24(w(){i=Y.41(i,q.9b+q.9c+1c($(q).17(\'9d-3Z\'),10))});a.Q.17(\'1b\',i)}I(q.K(a,\'2i\')||(b.1m+a.Q.1b()-g)>e){b.1m=Y.41((c?0:g),d[0]+(a.P?a.P.1b():0)-(c?g:0)-a.Q.1b()-(c&&$.1t.2s?1u.1H.2Z:0))}19{b.1m-=(c?g:0)}I((b.1k+a.Q.1y()-h)>f){b.1k=Y.41((c?0:h),d[1]-(c?h:0)-a.Q.1y()-(c&&$.1t.2s?1u.1H.30:0))}19{b.1k-=(c?h:0)}N b},3V:w(a){9e(a&&(a.2l==\'5I\'||a.9f!=1)){a=a.9g}z b=$(a).3U();N[b.1m,b.1k]},1O:w(a,b){z c=q.1r;I(!c||(a&&c!=$.1A(a,p))){N}I(q.1n){b=(b!=L?b:q.K(c,\'3B\'));z d=q.K(c,\'3z\');I(b!=\'\'&&$.32&&$.32[d]){c.Q.37(d,q.K(c,\'3A\'),b)}19{c.Q[(b==\'\'?\'37\':(d==\'9h\'?\'9i\':(d==\'9j\'?\'9k\':\'37\')))](b)}}z e=q.K(c,\'5h\');I(e){e.29((c.P?c.P[0]:L),[(c.1j?c.J:c.P.1I()),c])}I(q.1n){q.1n=Z;q.2R=L}q.1r=L},5N:w(a){I(!$.v.1r){N}z b=$(a.1S);I(!b.2W().5O().9l(\'#\'+$.v.3D)&&!b.21($.v.1F)&&!b.2W().5O().21($.v.1G)&&$.v.1n){$.v.1O(L,\'\')}},5j:w(){I($.v.1r&&$.v.1r.P){$.v.1r.P.1w()}},2S:w(e){z a=Z;z b=$.1A(e.1S,p);z c=(b&&b.1j?$(e.1S).5P()[0]:L);I(e.2v==9){$.v.2H.9m(11,11);$.v.1O(L,\'\');I(b&&b.1j){b.P.5x()}}19 I($.v.1n||(c&&!$.v.2p(c))){I(e.2v==18){I(!$.v.2c){b.Q.1Q(\'.\'+$.v.2L).2F();$.v.2c=11}a=11}19{z d=$.v.2A[e.2v];I(d){$(\'1f[20=\'+d+\']\',b.Q).5Q(\':1p\').1M();a=11}}}19 I(e.2v==36&&e.9n&&b&&!b.1j){$.v.22(q)}I(a){e.9o();e.9p()}N!a},2T:w(e){I($.v.2c){z a=$.1A(e.1S,p);a.Q.1Q(\'.\'+$.v.2L).37();$.v.2c=Z}},2U:w(e){z a=$.1A(e.1S,p);I(!a){N 11}z b=(a&&a.1j?$(e.1S).5P()[0]:L);z c=9q.9r(e.5R==42?e.2v:e.5R);z d=$.v.K(a,\'1d\');z f=$.v.K(a,\'1Y\');z g=$.v.K(a,\'3y\');I(!$.v.1n&&!b&&(g==\'9s\'||g==\'5v\')&&c>\' \'&&(\'9t\'.2a(0,d)+\'.\'+f).38(c.2N())==-1&&!(c==\'-\'&&a.P.1I()==\'\')){$.v.22(q);$.v.1n=11}I($.v.1n||(b&&!$.v.2p(b))){z h=$.v.2B[c==f?\'.\':c];I(h){$(\'1f[20=\'+h+\']\',a.Q).5Q(\':1p\').1M()}N Z}I($.v.K(a,\'5f\')){z i=(a.P.1I()+c).1g(/^0(\\d)/,\'$1\').1g(1B 26(\'^(-?)([\\\\.\'+f+\'])\'),\'$10$2\');z j=(f!=\'.\'?i.1g(1B 26(f),\'.\'):i);j=(d==10?2t(j):1c(j,d));z k=j.5S(d).1g(/\\./,f)+(c==f&&a.P.1I().38(f)==-1?c:\'\');k=(i.39(0)==\'-\'&&k.39(0)!=\'-\'?\'-\':\'\')+k;N c!=\' \'&&(c<\' \'||i==k||(!a.P.1I()&&(f+\'.-\').38(c)>-1))}N 11},K:w(a,b){N a.1N[b]!==42?a.1N[b]:q.2E[b]},5J:w(a){z b=q.K(a,\'2i\');z c=q.K(a,\'3C\');z d=q.K(a,\'5c\');z e=q.K(a,\'1d\');z f=q.K(a,\'2j\');z g=(!c?\'\':\'<1a 1i="v-3C">\'+c+\'</1a>\')+\'<1a 1i="v-5l\'+(a.3S?\' \'+q.2K:\'\')+\'"><1x>\'+a.12+\'</1x></1a>\';2g(z i=0;i<d.1R;i++){g+=\'<1a 1i="v-5M">\';2g(z j=0;j<d[i].1R;j+=2){z h=d[i].2a(j,2);z l=q.1o[h]||q.1o[\'??\'];z m=(l[0].39(0)==\'#\'?q.K(a,l[0].2a(1)+\'9u\'):l[0]);z n=(l[0].39(0)==\'#\'?q.K(a,l[0].2a(1)+\'9v\'):\'\');z o=(l[3]?l[3].9w(\' \'):[]);2g(z k=0;k<o.1R;k++){o[k]=\'v-\'+o[k]}o=o.9x(\' \');g+=(l[1]==q.1W?\'<1x 1i="v-\'+l[3]+\'"></1x>\':(a.1j&&(l[2]==\'.3r\'||l[2]==\'.3s\')?\'\':\'<1f 2l="1f" 20="\'+h+\'"\'+(l[1]==q.1e?\' 1i="v-9y\'+(l[0].1g(/^#1d/,\'\')==e?\' v-1d-43\':\'\')+(l[0]==\'#2x\'&&f?\' v-2y-43\':\'\')+(l[0]==\'#2z\'&&!f?\' v-2y-43\':\'\'):(l[1]==q.V?(1c(l[0],16)>=e||(e!=10&&l[0]==\'.\')?\' 1p="1p"\':\'\')+\' 1i="v-V\':(l[1]==q.1v?\' 1i="v-5T\':\' 1i="v-5T\'+(l[0].9z(/^#1K(56|57)$/)&&!a.15?\' v-1K-5y\':\'\'))))+(o?\' \'+o:\'\')+\'" \'+(n?\'3O="\'+n+\'"\':\'\')+\'>\'+(h==\'14.\'?q.K(a,\'1Y\'):m)+(l[5]&&l[5]!=l[0]?\'<1x 1i="\'+q.2L+(l[6]?\' v-9A\':\'\')+\'">\'+(l[6]||l[5])+\'</1x>\':\'\')+\'</1f>\'))}g+=\'</1a>\'}g+=\'<1a 3E="2f: 3M;"></1a>\'+(!a.1j&&$.1t.3W&&1c($.1t.3X,10)<7?\'<31 3N="9B:Z;" 1i="\'+q.2M+\'"></31>\':\'\');g=$(g);g.1Q(\'1f\').5U(w(){$(q).2o(\'v-44-45\')}).9C(w(){$(q).2q(\'v-44-45\')}).9D(w(){$(q).2q(\'v-44-45\')}).1M(w(){$.v.5V(a,$(q))});N g},28:w(a){z b=q.K(a,\'5e\');z c=1B 5W(a.J).5X(b).5Y();z d=c.1g(/^.+(e.+)$/,\'$1\').1g(/^[^e].*$/,\'\');I(d){c=1B 5W(c.1g(/e.+$/,\'\')).5X(b).5Y()}N 2t(c.1g(/0+$/,\'\')+d).5S(q.K(a,\'1d\')).9E().1g(/\\./,q.K(a,\'1Y\'))},1z:w(a,b){z c=q.K(a,\'5g\');I(c){c.29((a.P?a.P[0]:L),[b,a.12,a])}},5V:w(a,b){z c=q.1o[b.2n(\'20\')];I(!c){N}z d=b.2P().2a(0,b.2P().1R-b.3T(\'.v-20\').2P().1R);9F(c[1]){3a q.1e:c[2].29(q,[a,d]);3b;3a q.V:q.5Z(a,d);3b;3a q.1v:q.60(a,c[2],d);3b;3a q.W:q.47(a,c[2],d);3b}I($.v.1n||a.1j){a.P.1w()}},1E:w(a){},5Z:w(a,b){z c=q.K(a,\'1Y\');I(b==c&&a.12.38(b)>-1){N}a.12=((a.1C?\'\':a.12)+b).1g(/^0(\\d)/,\'$1\').1g(1B 26(\'^(-?)([\\\\.\'+c+\'])\'),\'$10$2\');I(c!=\'.\'){a.12=a.12.1g(1B 26(\'^\'+c),\'0.\')}z d=q.K(a,\'1d\');z e=(c!=\'.\'?a.12.1g(1B 26(c),\'.\'):a.12);a.J=(d==10?2t(e):1c(e,d));a.1C=Z;q.1z(a,b);q.1l(a)},60:w(a,b,c){I(!a.1C&&a.1h){a.1h(a);z d=q.K(a,\'1d\');a.J=(d==10?a.J:Y.48(a.J));a.12=q.28(a)}a.1q=a.J;a.1C=11;a.1h=b;q.1z(a,c);q.1l(a)},3g:w(a){a.J=a.1q+a.J},3h:w(a){a.J=a.1q-a.J},3i:w(a){a.J=a.1q*a.J},3j:w(a){a.J=a.1q/a.J},4r:w(a){a.J=Y.9G(a.1q,a.J)},47:w(a,b,c){a.1C=11;b.29(q,[a]);z d=q.K(a,\'1d\');a.J=(d==10?a.J:Y.48(a.J));a.12=q.28(a);q.1z(a,c);q.1l(a)},4g:w(a){a.J=-1*a.J;a.12=q.28(a);a.1C=Z},4f:w(a){a.J=Y.2d},4d:w(a){I(a.1h==q.3g){a.J=a.1q*(1+a.J/3c)}19 I(a.1h==q.3h){a.J=a.1q*(1-a.J/3c)}19 I(a.1h==q.3i){a.J=a.1q*a.J/3c}19 I(a.1h==q.3j){a.J=a.1q/a.J*3c}a.2u=a.1h;a.1h=q.1E},3k:w(a){I(a.1h==q.1E){I(a.2u!=q.1E){a.1q=a.J;a.J=a.3Y;a.2u(a)}}19{a.2u=a.1h;a.3Y=a.J;a.1h(a);a.1h=q.1E}},4G:w(a){a.15+=a.J},4H:w(a){a.15-=a.J},4F:w(a){a.15=a.J},4D:w(a){a.J=a.15},4B:w(a){a.15=0},4u:w(a){q.3d(a,Y.3l)},4v:w(a){q.3d(a,Y.3m)},4w:w(a){q.3d(a,Y.3n)},3d:w(a,b){z c=q.K(a,\'2j\');a.J=b(a.J*(c?Y.2d/61:1))},4x:w(a){q.3e(a,Y.3o)},4y:w(a){q.3e(a,Y.3p)},4z:w(a){q.3e(a,Y.3q)},3e:w(a,b){a.J=b(a.J);I(q.K(a,\'2j\')){a.J=a.J/Y.2d*61}},4h:w(a){a.J=1/a.J},4i:w(a){a.J=Y.2e(a.J)/Y.2e(10)},4l:w(a){a.J=Y.2e(a.J)},4m:w(a){a.J=Y.4n(a.J)},4o:w(a){a.J*=a.J},4p:w(a){a.J=Y.4q(a.J)},4s:w(a){a.J=Y.4t()},4J:w(a,b){q.2w(a,b,2)},4L:w(a,b){q.2w(a,b,8)},4N:w(a,b){q.2w(a,b,10)},4P:w(a,b){q.2w(a,b,16)},2w:w(a,b,c){a.1N.1d=c;a.J=(c==10?a.J:Y.48(a.J));a.12=q.28(a);a.1C=11;q.1z(a,b);q.1l(a)},4Q:w(a,b){q.49(a,b,11)},4R:w(a,b){q.49(a,b,Z)},49:w(a,b,c){a.1N.2j=c;q.1z(a,b);q.1l(a)},4T:w(a,b){a.12=a.12.2a(0,a.12.1R-1)||\'0\';z c=q.K(a,\'1d\');a.J=(c==10?2t(a.12):1c(a.12,c));q.1z(a,b);q.1l(a)},4V:w(a,b){a.12=\'0\';a.J=0;a.1C=11;q.1z(a,b);q.1l(a)},4X:w(a,b){q.2m(a,0,Z);q.1z(a,b);q.1l(a)},3r:w(a,b){q.3f(a,b,a.P.1I())},50:w(a,b){I(a.1h!=q.1E){q.47(a,q.3k,b)}q.3f(a,b,a.12)},3s:w(a,b){q.2m(a,0,Z);q.1l(a);q.3f(a,b,\'\')},3f:w(a,b,c){I(a.1j){q.1r=a}19{a.P.1I(c)}q.1z(a,b);q.1O(a.P[0])}});w 3J(a,b){$.2G(a,b);2g(z c 3t b){I(b[c]==L||b[c]==42){a[c]=b[c]}}N a};$.9H.v=w(a){z b=9I.5k.9J.9K(9L,1);I(a==\'9M\'){N $.v[\'14\'+a+\'2b\'].29($.v,[q[0]].62(b))}N q.24(w(){2h a==\'5D\'?$.v[\'14\'+a+\'2b\'].29($.v,[q].62(b)):$.v.5r(q,a)})};$.v=1B 2b();$(w(){$(1u.40).2J($.v.2H).5U($.v.5N)})})(9N);',62,608,'||||||||||||||||||||||||||this|||||calculator|function|||var|||||||||if|curValue|_get|null||return||_input|_mainDiv|||||digit|unary||Math|false||true|dispValue||_|memory||css||else|div|width|parseInt|base|control|button|replace|_pendingOp|class|_inline|top|_updateCalculator|left|_showingCalculator|_keyDefs|disabled|prevValue|_curInst|the|browser|document|binary|focus|span|height|_sendButton|data|new|_newValue|_disabledFields|_noOp|markerClassName|_triggerClass|documentElement|val|arith|mem|to|click|settings|_hideCalculator|end|find|length|target|_pos|hex|trig|space||decimalChar|Switch|keystroke|hasClass|_showCalculator|siblings|each|px|RegExp||_setDisplay|apply|substr|Calculator|_showingKeystrokes|PI|log|clear|for|typeof|isRTL|useDegrees|_inlineEntryClass|type|_reset|attr|addClass|_isDisabledCalculator|removeClass|unbind|opera|parseFloat|_savedOp|keyCode|_changeBase|degrees|angle|radians|_keyCodes|_keyChars|value|Erase|_defaults|show|extend|mainDiv|_inlineClass|append|_focussedClass|_keystrokeClass|_coverClass|toLowerCase|input|text|img|_lastInput|_doKeyDown|_doKeyUp|_doKeyPress|remove|parents|position|_getExtras|scrollLeft|scrollTop|iframe|effects|border|padding|||hide|indexOf|charAt|case|break|100|_trig|_atrig|_finished|_add|_subtract|_multiply|_divide|_equals|sin|cos|tan|asin|acos|atan|_close|_erase|in|number|regional|from|last|showOn|showAnim|showOptions|duration|prompt|_mainDivId|style|display|_appendClass|_disableClass|_resultClass|extendRemove|nodeName|bind|both|src|title|keydown|keyup|keypress|_focussed|children|offset|_findPos|msie|version|_savedValue|right|body|max|undefined|active|key|down||_unaryOp|floor|_degreesRadians|decimal|add|subtract|_percent|pi|_pi|_plusMinus|_inverse|_log|LN|ln|_ln|_exp|exp|_sqr|_sqrt|sqrt|_power|_random|random|_sin|_cos|_tan|_asin|_acos|_atan|MC|_memClear|MR|_memRecall|MS|_memStore|_memAdd|_memSubtract|base2|_base2|base8|_base8|base10|_base10|base16|_base16|_degrees|_radians|BS|_undo|CE|_clearError|CA|_clear|close|use|_use|erase|buttonText|buttonStatus|Close|Use|Clear|Recall|buttonImage|buttonImageOnly|appendText|calculatorClass|layout|standardLayout|precision|constrainInput|onButton|onClose|none|_focusEntry|prototype|result|BSCECA|_1_2_3_|_4_5_6_|_7_8_9_|_0_|_attachCalculator|_connectCalculator|before|after|opbutton|html|blur|empty|removeData|opacity|cursor|map|string|fixed|absolute|1000px|_checkOffset|hidden|_generateHTML|bottom|window|row|_checkExternalClick|andSelf|parent|not|charCode|toString|oper|mousedown|_handleButton|Number|toFixed|valueOf|_digit|_binaryOp|180|concat|_0|_1|_2|_3|_4|_5|_6|_7|_8|_9|_A|_B|_C|_D|_E|_F|DECIMAL|ADD|SUBTRACT|multiply|MULTIPLY|divide|DIVIDE|percent|PERCENT|equals|EQUALS|plus|minus|PLUS_MINUS|inverse|INV|LG|LOG|EX|EXP|SQ|sqr|SQR|SR|SQRT|XY|power|POWER|RN|rnd|RANDOM|SN|SIN|CS|COS|TN|TAN|AS|ASIN|AC|ACOS|AT|ATAN|memClear|MEM_CLEAR|memRecall|recall|MEM_RECALL|memStore|store|MEM_STORE|memAdd|MEM_ADD|memSubtract|MEM_SUBTRACT|BB|BASE_2|BO|BASE_8|BD|BASE_10|BH|BASE_16|DG|DEGREES|RD|RADIANS|backspace|undo|UNDO|BSp|clearError|error|CLEAR_ERROR|Hom|CLEAR|End|CLOSE|Esc|USE|Ent|ERASE|Del|SPACE|half|HALF_SPACE|Open|closeText|closeStatus|useText|useStatus|current|eraseText|eraseStatus|field|backspaceText|backspaceStatus|clearErrorText|clearErrorStatus|clearText|clearStatus|Reset|memClearText|memClearStatus|memRecallText|memRecallStatus|memStoreText|memStoreStatus|Store|memAddText|memAddStatus|Add|memSubtractText|memSubtractStatus|Subtract|base2Text|Bin|base2Status|base8Text|Oct|base8Status|octal|base10Text|Dec|base10Status|base16Text|Hex|base16Status|hexadecimal|degreesText|Deg|degreesStatus|radiansText|Rad|radiansStatus|normal|id|hasCalculator|inline|trigger|keyentry|focussed|cover|scientificLayout|DGRD|MC_|SNASSRLG_|MR_|CSACSQLN_|MS_|TNATXYEX_|PIRN1X|setDefaults|addKeyDef|boolean|alt|setData|getData|_destroyCalculator|prev|_enableCalculator|_disableCalculator|default|relative|prepend|inArray|_changeCalculator|offsetHeight|block|auto|rtl|thin|medium|thick|innerWidth|clientWidth|innerHeight|clientHeight|offsetLeft|offsetWidth|margin|while|nodeType|nextSibling|slideDown|slideUp|fadeIn|fadeOut|is|stop|ctrlKey|preventDefault|stopPropagation|String|fromCharCode|operator|0123456789abcdef|Text|Status|split|join|ctrl|match|keyname|javascript|mouseup|mouseout|toUpperCase|switch|pow|fn|Array|slice|call|arguments|isDisabled|jQuery'.split('|'),0,{}))