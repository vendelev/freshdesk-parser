FROM node:22-alpine

RUN <<EOF
  set -e
  npm install -g @fission-ai/openspec@latest
EOF

USER node

WORKDIR /var/www

CMD ["tail", "-f", "/dev/null"]
