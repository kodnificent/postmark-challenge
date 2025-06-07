window.filamentChartJsPlugins = [
    {
        id: 'textCenter',
        beforeDatasetDraw(chart, args, pluginOptions) {
            const { ctx } = chart;
            const meta = chart.getDatasetMeta(0);
            if (!meta.data[0]) return;

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = pluginOptions.color ?? (isDark ? '#f9fafb' : '#111827');

            ctx.save();
            ctx.font = 'bolder 50px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = textColor;
            ctx.fillText(
                pluginOptions.text ?? '',
                meta.data[0].x,
                meta.data[0].y
            );
            ctx.restore();
        },
    }
];
