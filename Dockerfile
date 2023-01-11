FROM ubuntu:20.04
ARG DEBIAN_FRONTEND=noninteractive
# RUN sed -i s@/archive.ubuntu.com/@/mirrors.bupt.edu.cn/@g /etc/apt/sources.list
# RUN sed -i s@/security.ubuntu.com/@/mirrors.bupt.edu.cn/@g /etc/apt/sources.list
RUN apt clean  \
    && apt update -y  \
    && apt install -y libgmp-dev m4 flex bison libssl-dev libglib2.0-dev wget build-essential php libapache2-mod-php
WORKDIR /root
RUN wget https://crypto.stanford.edu/pbc/files/pbc-0.5.14.tar.gz  \
    && tar zxf pbc-0.5.14.tar.gz
WORKDIR /root/pbc-0.5.14
RUN ./configure  \
    && make  \
    && make install
WORKDIR /root
RUN wget http://acsc.cs.utexas.edu/cpabe/libbswabe-0.9.tar.gz  \
    && tar zxf libbswabe-0.9.tar.gz
WORKDIR /root/libbswabe-0.9
RUN ./configure  \
    && make  \
    && make install
WORKDIR /root
# RUN wget http://acsc.cs.utexas.edu/cpabe/cpabe-0.11.tar.gz  \
#     && tar zxf cpabe-0.11.tar.gz
WORKDIR /root/cpabe
COPY cpabe-0.11/* ./
RUN ./configure
RUN sed -i 's/-lcrypto -lcrypto /-lcrypto -lcrypto -lgmp /g' Makefile
RUN make
RUN make install
RUN cpabe-setup -p /tmp/pub_key -m /tmp/master_key
COPY php/* /var/www/html/
CMD /etc/init.d/apache2 start && bash