- name: Deploy Docker image to VPS
        uses: appleboy/ssh-action@master
        with:
          username: ${{ secrets.SSH_USERNAME }}
          password: ${{ secrets.SSH_PASSWORD }}
          host: ${{ secrets.SSH_HOST }}
          port: ${{ secrets.SSH_PORT }}
          script: |
            cd /home/sylvester/loyalty
            chmod +x deploy/deploy.sh
            ./deploy/deploy.sh
