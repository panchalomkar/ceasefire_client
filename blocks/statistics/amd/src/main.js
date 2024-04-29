
define(['jquery', 'block_statistics/chart'], function ($, Chart) {
    Chart.pluginService.register({
            beforeDraw: function (chart,size) {
                
            if (chart.config.options.elements.center) {
                    //Get ctx from string
                    var ctx = chart.chart.ctx;

                    //Get options from the center object in options
                    var centerConfig = chart.config.options.elements.center;
                    var fontStyle = centerConfig.fontStyle || 'Roboto';
                    var txt = centerConfig.text;
                    var color = centerConfig.color || '#67747b';
                    var sidePadding = centerConfig.sidePadding || 20;
                    var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
                    //Start with a base font of 30px
                    ctx.font = "30px " + fontStyle;  
                    //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                    var stringWidth = ctx.measureText(txt).width;
                    var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                    // Find out how much the font can grow in width.
                    var widthRatio = elementWidth / stringWidth;
                    var newFontSize = Math.floor(30 * widthRatio);
                    var elementHeight = (chart.innerRadius * 2);

                    // Pick a new font size so it will not be larger than the height of label.
                    var fontSizeToUse = Math.min(newFontSize, elementHeight);

                    //Set font settings to draw it correctly.
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
                    var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 2);
                    //ctx.font = fontSizeToUse+"px " + fontStyle;
                    ctx.font = "30px " + fontStyle;
                    ctx.fillStyle = '#67747b';

                    //console.log(ctx.font)
                    //Draw text in center
                    ctx.fillText(txt, centerX, centerY);
            }
            
            if (chart.config.options.elements.secondaryTxt) {
                //Get ctx from string
                var ctx = chart.chart.ctx;

                //Get options from the center object in options
                var centerConfig = chart.config.options.elements.secondaryTxt;
                var fontStyle = centerConfig.fontStyle || 'Roboto';
                var txt = centerConfig.text;
                var a = txt.split(" ");
                var string = a[1]+' '+ a[2];
                var color = centerConfig.color || '#768c94';
                var sidePadding = centerConfig.sidePadding || 20;
                var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
                //Start with a base font of 30px
                //ctx.font = fontSizeToUse+"px " + fontStyle;s
                ctx.font = "11px " + fontStyle;

                //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                var stringWidth = ctx.measureText(txt).width;
                var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                // Find out how much the font can grow in width.
                var widthRatio = elementWidth / stringWidth;
                var newFontSize = Math.floor(30 * widthRatio);
                var elementHeight = (chart.innerRadius * 2);

                // Pick a new font size so it will not be larger than the height of label.
                var fontSizeToUse = Math.min(newFontSize, elementHeight);

                //Set font settings to draw it correctly.
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                var centerX = ((chart.chartArea.left + chart.chartArea.right) / 1.6);
                var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 1.45);
                ctx.font = "12px" + fontStyle;
                ctx.fillStyle = color;
                
                if(txt == '')
                {
                    ctx.fillText(txt, centerX, centerY);        
                }else{
                    ctx.fillText(string, centerX, centerY);    
                }
                
                    
            }
            if (chart.config.options.elements.threeTxt) {
                //Get ctx from string
                var ctx = chart.chart.ctx;

                //Get options from the center object in options
                var centerConfig = chart.config.options.elements.threeTxt;
                var fontStyle = centerConfig.fontStyle || 'Roboto';
                var txt = centerConfig.text;
                var a = txt.split(" ");
                var string = a[0];
                var color = centerConfig.color || '#575757';
                var sidePadding = centerConfig.sidePadding || 20;
                var sidePaddingCalculated = (sidePadding/100) * (chart.innerRadius * 2)
                //Start with a base font of 30px
                //ctx.font = fontSizeToUse+"px " + fontStyle;s
                ctx.font = "bold 12px " + fontStyle;

                //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
                var stringWidth = ctx.measureText(txt).width;
                var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

                // Find out how much the font can grow in width.
                var widthRatio = elementWidth / stringWidth;
                var newFontSize = Math.floor(30 * widthRatio);
                var elementHeight = (chart.innerRadius * 2);

                // Pick a new font size so it will not be larger than the height of label.
                var fontSizeToUse = Math.min(newFontSize, elementHeight);

                //Set font settings to draw it correctly.
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                var centerX = ((chart.chartArea.left + chart.chartArea.right) / 3);
                var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 1.45);
                ctx.font = "12px" + fontStyle;
                ctx.fillStyle = color;
                
                    ctx.fillText(string, centerX, centerY);    
                
                    
            }

            
        },
        afterDraw: function(chart,size){
            chart.canvas.parentNode.style.height = '126px';
            chart.canvas.parentNode.style.width = '126px'
            chart.options.title.fontSize = 13;
            chart.update();
        }
    });
    
    return {
        init: function (data) {
        $.each(data, function( index, content ) {
            var ctx = document.getElementById(index).getContext('2d');
            //if($(window).width() >= 1520){
            //ctx.height = '230px';
            //ctx.width = '230px';
            //}
            var gradientStroke = ctx.createLinearGradient(500, 0, 100, 0);
            gradientStroke.addColorStop(0, content.fromcolor);
            gradientStroke.addColorStop(1, content.tocolor);
        var inverse = 0;
        if(content.inverse){
            inverse = content.inverse;
        }
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                    labels: [content.title],
                datasets: [{
                        label: content.title,
                        data: [content.value,inverse],
                    backgroundColor: [
                        gradientStroke,
                        '#c1c1c1',
                    ],
                    borderColor: [
                        gradientStroke,
                        '#c1c1c1',
                    ],
                    borderWidth: 1
                }]
            },
            axis: {
                display: false
            },
            options: {
//                responsive: false,
                tooltips: {
                    enabled: false
                },
                layout: {
                    padding: {
                        left: 1,
                        right: 1,
                        top: 1,
                        bottom: 1
                    }
                },
                hover: {
                    mode: null
                },
                title: {
                    text: [content.title,content.type],
                    display: false,
                    position: 'bottom',
                    fontSize: 13,
                    fontWeight: 'bold'
                },
                elements: {
                    center: {
                        text: content.showednumber,
                        color: content.txtColor, // Default is #000000
                        fontStyle: 'Roboto', // Default is Arial
                        sidePadding: content.padding, // Defualt is 20 (as a percentage)
                        fontSize: 12
                    },
                    secondaryTxt: {
                        text: content.description,
                        color: content.txtColor, // Default is #000000
                        fontStyle: 'Roboto', // Default is Arial
                        sidePadding: 20, // Defualt is 20 (as a percentage)
                        fontSize: 12
                    },
                        threeTxt: {
                        text: content.description,
                        color: content.txtColor, // Default is #000000
                        fontStyle: 'Roboto', // Default is Arial
                        sidePadding: 20, // Defualt is 20 (as a percentage)
                        fontSize: 12
                    }
                },
                cutoutPercentage: 95,
                scales: {
                    yAxes: [{
                        gridLines: {
                            drawBorder: false,
                            display: false,
                        },
                        ticks: {
                            display: false
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            drawBorder: false,
                            display: false,
                        },
                        ticks: {
                            display: false
                        }
                    }],
                },
                legend: {
                    display: false,
                }
            },
        });
        });
        }
    };
});