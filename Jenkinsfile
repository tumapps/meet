pipeline {
    agent any

    environment {
        GIT_CREDENTIALS = 'github-creds'
        DEPLOY_PATH = '/var/www/html/meet'
    }

    stages {
        stage('Clone Vue3 Repo') {
            steps {
                git credentialsId: "${GIT_CREDENTIALS}",
            url: 'https://github.com/kiavin/scheduler.git',
            branch: 'deployment'
            }
        }

        stage('Install NPM Dependencies') {
            steps {
                sh '''
          npm install
        '''
            }
        }

        stage('Build Vue App') {
            steps {
                sh '''
          npm run build
        '''
            }
        }

        stage('Deploy to Web Directory') {
            steps {
                sh '''
          sudo mkdir -p ${DEPLOY_PATH}
          sudo rm -rf ${DEPLOY_PATH}/*
          sudo rsync -avz --delete dist/ ${DEPLOY_PATH}/
        '''
            }
        }
    }
}
