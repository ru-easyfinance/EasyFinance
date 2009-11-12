package
{
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.display.Sprite;
    import flash.display.BitmapData;
    import flash.display.Loader;
    import flash.net.URLRequest;
	import flash.display.JointStyle;
	import flash.text.TextField;
	import flash.text.TextFormat;
	
	/**
	 * ...
	 * @author Andrey Y
	 */
	public class Tooltip extends MovieClip
	{
		public var filler:String = "#ffffcd";
		public var borderColor:uint = 0xC6C6C6;
		
		private var loader:Loader = new Loader();
		
		var _txt:TextField = new TextField();
		
		var textFormat = new TextFormat();
		
		public function Tooltip(txt:String, _x, _y ) 
		{
			this.x = _x;
			this.y = _y - 12;
			
			
			
			_txt.height = 15;
			
			_txt.multiline = true;
			
			this.mouseEnabled = false;
			this._txt.selectable = false;
			
			textFormat.size = 11;
			textFormat.font = "Verdana";
			
			try
			{
				_txt.text = txt;
			}
			catch (e:Error)
			{
				
			}
			
			_txt.setTextFormat(textFormat);
			_txt.width = this._txt.textWidth+5;
			_txt.height = this._txt.textHeight+20;
			
			if ((_x + _txt.width) >= 320)
			{
				trace(',bigger');
				this.x = 320 - _txt.width;
			}
			
			addChild(_txt);
			
			redraw();
			//trace();
			_txt.y -= 3;
		}
		
		public function redraw()
		{			
			if (filler.charAt(0) == '#')
			{
				var color = filler.substr(1, 6);

				color = uint("0x" + color);
			
				this.graphics.beginFill(color, 1);
				this.graphics.lineStyle(1, borderColor);
				this.graphics.drawRect(0, 0, this._txt.textWidth+3, this._txt.textHeight);
				this.graphics.endFill();
			}
			else
			{
				var request:URLRequest = new URLRequest(filler);
				
				loader.load(request);
				loader.contentLoaderInfo.addEventListener(Event.COMPLETE, drawImage);
			}			
		}
		
		public function drawImage(ev:Event)
		{
				var myBitmap:BitmapData = new BitmapData(loader.width, loader.height, false);
				
				myBitmap.draw(loader);
								
				this.graphics.beginBitmapFill(myBitmap);
				this.graphics.lineStyle(1, borderColor);
				this.graphics.drawRect(0, 0, this._txt.textWidth, this.height);
				this.graphics.endFill();
		}
		
	}
	
}