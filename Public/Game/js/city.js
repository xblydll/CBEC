!function(){
	var citys=[{
		"n":"湖南",
		"c":[{
			"a":["芙蓉区","岳麓区","雨花区","开福区","天心区","浏阳市","长沙县","宁乡县","望城区"],
			"n":"长沙市"
		}]
	}];
	if(typeof define==="function"){
		define(citys)
	}else{
		window.YDUI_CITYS=citys
	}
}();