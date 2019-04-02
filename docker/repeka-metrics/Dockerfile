FROM kamon/grafana_graphite

RUN git clone https://github.com/grafana/piechart-panel.git /opt/grafana/plugins/piechart-panel \
	&& git clone https://github.com/mtanda/grafana-histogram-panel.git /opt/grafana/plugins/histogram-panel

COPY config/custom.ini* /opt/grafana/conf/
COPY storage-schemas.conf /opt/graphite/conf/storage-schemas.conf
COPY statsd-config.conf /src/statsd/config.js
