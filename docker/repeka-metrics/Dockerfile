FROM kamon/grafana_graphite

# Set root_url for graphana to respect the ProxyPass redirection
RUN sed -i.bak 's/;root_url =.*/root_url = %(protocol)s:\/\/%(domain)s:%(http_port)s\/grafana\//' /opt/grafana/conf/custom.ini

RUN echo [paths] >> /opt/grafana/conf/custom.ini \
	&& echo plugins=/opt/grafana/plugins >> /opt/grafana/conf/custom.ini

RUN git clone https://github.com/grafana/piechart-panel.git /opt/grafana/plugins/piechart-panel \
	&& git clone https://github.com/mtanda/grafana-histogram-panel.git /opt/grafana/plugins/histogram-panel
