var SM = new Class({
	Title: '',
	Msg: '',
	MsgID: 0,
	Url: '',
	El: '',
	Close: '',
	Effect: {},
	Array: '',
	
	initialize: function() {},
	
	Start: function() {
		this.El = this.ConstructEl();
		this.PopulateEl();
		this.El.injectInside($$('body')[0]);
		
		this.Effect = new Fx.Slide(this.El, {
			duration: 2000,
			transition: Fx.Transitions.Bounce.easeOut,
			onStart: function(el) {
				this.wrapper.setStyle('position', 'absolute');
				this.wrapper.setStyle('top', '0px');
				var Left = (Math.ceil((Window.getWidth() / 2) - (this.wrapper.getSize().size.x / 2))) + 'px';
				this.wrapper.setStyle('left', Left);
			}
		});
		
		this.Effect.hide();
		this.Effect.slideIn();
	},
	
	PopulateEl: function() {
		this.El.getElement('h2').innerHTML = this.Title;
		this.El.getElement('p').innerHTML = this.Msg;
		this.El.getElement('a').innerHTML = this.Close;
		this.El.getElement('a').addEvent('click', this.Read.bindAsEventListener(this));
	},
	
	Read: function(evt) {
		this.Effect.slideOut();
		var Jax = new Ajax(this.Url, { data: 'MsgID=' + this.MsgID + '&Read=' + this.Array });
		Jax.request();
	},
	
	ConstructEl: function() {
		return new Element('div', { id: 'SMDropDown' }).adopt(
			new Element('div', { id: 'SMDropDownFirst' }).adopt(
				new Element('div', { id: 'SMDropDownSecond' }).adopt([
					new Element('h2'),
					new Element('p'),
					new Element('a', { id: 'SMCloseLink', href: 'javascript:void(0);' })
				])
			)
		);
	}
});

var SysMsg = new SM();

Window.addEvent('load', function() {
	SysMsg.Start();
});