var SMToField = new Class({
	El: '',
	initialize: function(el) {
		this.El = el;
	},
	
	Add: function(what) {
		if(!this.Exists(what)) {
			if(this.El.value != '') this.El.value += ', ';
			this.El.value += what;
		}
	},
	
	Exists: function(what) {
		var Current = this.El.value.split(', ');
		return Current.contains(what);
	},
	
	Remove: function(what) {
		var Current = this.El.value.split(', ');
		Current.remove(what);
		this.El.value = Current.join(', ');
	}
});

var SMUserManager = new Class({
	Search: '',
	Select: '',
	Users: [],
	initialize: function() {
		this.Search = $('SearchUser');
		this.Select = $('UserSelect');
		
		for(var i = 0; i < this.Select.options.length; i++) {
			this.Users.include(this.Select.options[i].value);
		}
		
		this.Search.addEvent('keyup', this.Typed.bindAsEventListener(this));
	},
	
	Typed: function(evt) {
		var Test = this.Search.value;
		if(Test == '') var NewUsers = this.Users;
		else {
			var NewUsers = this.Users.filter(function(user) {
				return user.test('^' + Test, 'i');
			});
		}
		
		var Options = '';
		for(var i = 0; i < NewUsers.length; i++) {
			Options += '<option>' + NewUsers[i] + '</option>';
		}
		
		this.Select.innerHTML = Options;
	}
});

var SMPageManager = new Class({
	Pages: [],
	Links: [],
	CurrentPage: 1,
	PerPage: 0,

	initialize: function(Pages) {
		this.Pages = Pages;
		if(Pages.length < 2) return;
		this.PerPage = this.Pages[0].getElements('dt').length;
		
		this.ClearPages();
		$('Page_1').setStyle('display', 'block');
		this.SanityCheck();
	},
	
	AttachEvents: function() {
		$$('#PageNums li a').each(function(ln) {
			if(ln['$events'] && ln['$events'].click.keys.length > 0) return;
			ln.addEvent('click', this.ChangePage.bindAsEventListener(this));
		}.bind(this));
	},
	
	ClearPages: function() {
		this.Pages.each(function(pg) {
			pg.setStyle('display', 'none');
		});
	},
	
	SanityCheck: function() {
		if(this.CurrentPage == 1 && !$('PageLeft').hasClass('inactive')) $('PageLeft').addClass('inactive');
		else if(this.CurrentPage > 1) $('PageLeft').removeClass('inactive');
		if(this.CurrentPage == this.Pages.length && !$('PageRight').hasClass('inactive')) $('PageRight').addClass('inactive');
		else if(this.CurrentPage < this.Pages.length) $('PageRight').removeClass('inactive');
		
		this.RecreatePageLinks();
		
		if(!$('PageLink_' + this.CurrentPage).hasClass('inactive')) $('PageLink_' + this.CurrentPage).addClass('inactive');
		
		this.AttachEvents();
		
		var MsgCountStart = ((this.CurrentPage - 1) * this.PerPage) + 1;
		var MsgCountEnd = MsgCountStart + this.PerPage - 1;
		if(MsgCountEnd > $$('#EditTab #ContentBody dt').length) MsgCountEnd = $$('#EditTab #ContentBody dt').length;
		$$('span.MsgCountStart').each(function(el) { el.innerHTML = MsgCountStart});
		$$('span.MsgCountEnd').each(function(el) { el.innerHTML = MsgCountEnd });
	},
	
	RecreatePageLinks: function() {
		var Items = $$('#PageNums li');
		var List = $('PageNums');
		var Before = Items.getLast();
		Items.each(function(item, k) {
			if(k == 0 || k == Items.length - 1) return;
			item.remove();
		});
		
		this.AddNewPageLink(1, Before);
		
		if(this.CurrentPage >= 6) {
			if(this.CurrentPage > 6) this.AddNewElipses(Before);
			for(var i = this.CurrentPage - 4; i < this.CurrentPage + 4; i++) {
				if(i > this.Pages.length) break;
				this.AddNewPageLink(i, Before);
			}
		} else {
			for(var i = 2; i < 9; i++) {
				if(i > this.Pages.length) break;
				this.AddNewPageLink(i, Before);
			}
		}
		
		if(this.Pages.length > 9 && this.CurrentPage < this.Pages.length - 4) {
			this.AddNewElipses(Before);
			this.AddNewPageLink(this.Pages.length, Before);
		} else if(this.CurrentPage == this.Pages.length - 4) {
			this.AddNewPageLink(this.Pages.length, Before);
		}
	},
	
	AddNewElipses: function(Before) {
		var li = new Element('li');
		li.textContent = '... ';
		li.injectBefore(Before);
	},
	
	AddNewPageLink: function(num, Before) {
		var el = this.NewPageLink();
		var ln = el.getChildren()[0];
		ln.setProperty('id', 'PageLink_' + num);
		ln.textContent = num + ' ';
		
		el.injectBefore(Before);
	},
	
	NewPageLink: function() {
		return new Element('li').adopt(
			new Element('a', {
				href: 'javascript:void(0);'
			})
		);
	},
	
	ChangePage: function(evt) {
		var Page = evt.target.textContent.trim();
		
		if(Page == '<') {
			if(this.CurrentPage == 1) return;
			Page = this.CurrentPage - 1;
		}
		
		if(Page == '>') {
			if(this.CurrentPage == this.Pages.length) return;
			Page = this.CurrentPage + 1;
		}
		
		this.ClearPages();
		$('Page_' + Page).setStyle('display', 'block');
		this.CurrentPage = Page.toInt();
		this.SanityCheck();
	}
});

var SMAdmin = new Class({
	ExpandEffect: {},
	Form: '',
	Translate: [],
	ToObj: {},
	UserObj: {},
	PageObj: {},
	mAccordion: {},
	Edit: false,
	Fail: false,

	initialize: function() {},
	
	Start: function() {
		this.Form = $$('#Form form')[1];
		this.ToObj = new SMToField(this.Form['to']);
		this.UserObj = new SMUserManager();
		
		if(this.Form['MsgID'].value != '') this.Edit = true;
		
		var Pages = $$('#ContentBody dl');
		for(var i = 1; i < Pages.length; i++) Pages[i].setStyle('display', 'none');
		this.PageObj = new SMPageManager(Pages);
		
		this.mAccordion = new Accordion('dt.Message', 'dd.MessageContent', {
			opacity: false,
			alwaysHide: true,
			onActive: function(toggler, element) {
				if(!toggler.hasClass('active')) {
					toggler.addClass('active');
				}
			},
			
			onBackground: function(toggler, element) {
				if(toggler.hasClass('active')) {
					toggler.removeClass('active');
				}
			}
		});
	
		$('ExpandedOptions').setStyle('display', 'block');
		this.ExpandEffect = new Fx.Slide('ExpandedOptions');
		this.ExpandEffect.hide();
		
		if($('DeleteButton')) {
			$('DeleteButton').addEvent('click', function() {
				var Delete = confirm('Are you sure?');
				if(Delete) $('DeleteForm').submit();
			});
		}
		
		this.Form['Irrelevant01'].selectedIndex = 0;
		
		// Stupid Firefox saving form values ¬_¬
		if(!this.Edit) {
			this.Form['to'].value = this.Translate[0];
			this.Form['NotRoles'].checked = false;
		} else {
			this.Form['to'].removeClass('Special');
			if(this.Form['NotRoles'].checked) this.ToggleForms();
		}
		
		if(this.Fail) this.Form['to'].addClass('Fail');
		
		this.Form['to'].addEvent('focus', function() {
			if(this.hasClass('Fail')) this.removeClass('Fail');
			if(!this.hasClass('Special')) return true;
			this.removeClass('Special');
			this.value = '';
		});
		
		this.Form['NotRoles'].addEvent('click', function() {
			el = this.Form['NotRoles'];
			if(this.Form['to'].hasClass('Special')) {
				if(el.checked) var Value = this.Translate[1];
				else var Value = this.Translate[0];
				this.Form['to'].value = Value;
			}
			this.ToggleForms();
		}.bind(this));
		
		this.Form['Irrelevant01'].addEvent('change', this.AddRole.bind(this));
		
		$('UserSelect').addEvent('change', this.AddUser.bind(this));
		
		$('ExpandButton').addEvent('click', function() {
			this.ExpandEffect.toggle();
		}.bind(this));
		
		$$('#Form div.legend a')[0].addEvent('click', function() {
			if(!this.hasClass('active')) this.addClass('active');
			if(this.getNext().hasClass('active')) this.getNext().removeClass('active');
			$('CreateTab').setStyle('display', 'block');
			$('EditTab').setStyle('display', 'none');
		});
		
		if(Pages.length > 0) {
			$$('#Form div.legend a')[1].addEvent('click', function() {
				if(!this.hasClass('active')) this.addClass('active');
				if(this.getPrevious().hasClass('active')) this.getPrevious().removeClass('active');
				$('CreateTab').setStyle('display', 'none');
				$('EditTab').setStyle('display', 'block');
			});
		} else $$('#Form div.legend a')[1].remove();
	},
	
	AddUser: function() {
		if(this.Form['to'].hasClass('Special')) {
			this.Form['to'].removeClass('Special');
			this.Form['to'].value = '';
		}
		
		var el = $('UserSelect');
		this.ToObj.Add(el.options[el.selectedIndex].value);
	},
	
	AddRole: function() {
		if(this.Form['to'].hasClass('Special')) {
			this.Form['to'].removeClass('Special');
			this.Form['to'].value = '';
		}
		
		var el = this.Form['Irrelevant01'];
		if(el.selectedIndex == 1) {
			for(var i = 3; i < el.options.length; i++) {
				this.ToObj.Add(el.options[i].value);
			}
		} else {
			this.ToObj.Add(el.options[el.selectedIndex].value);
		}
	},
	
	ToggleForms: function() {
		if($('ChoiceRoles').getStyle('display') != 'none') {
			$('ChoiceRoles').setStyle('display', 'none');
			$('ChoiceUsers').setStyle('display', 'block');
		} else {
			$('ChoiceRoles').setStyle('display', 'block');
			$('ChoiceUsers').setStyle('display', 'none');
		}
		this.ExpandEffect.show();
	}
});

var SysMsgAdministrate = new SMAdmin();

Window.addEvent('domready', function() {
	SysMsgAdministrate.Start();
});