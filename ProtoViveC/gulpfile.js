//Librerias parala funcion crop, reesacalar imagenes y convertir a WebP
import path from 'path'
import fs from 'fs'
import { glob } from 'glob'

import {src,dest,watch,series} from 'gulp'
import * as dartSass from 'sass'
import gulpSass from 'gulp-sass'

const sass = gulpSass(dartSass)
import sharp from 'sharp'

//Generar el archivo a procesar en la carpeta de build
//En esta carpeta estaran todos los documentos que usara la app en produccion
import terser from 'gulp-terser'

export function js( done ){
    src('src/**/*.js')
    .pipe(terser())
    .pipe( dest('build') )

    //Done para terminar las tareas
    done()
}

//Generar el css a partir de los archivos scss
export function css( done ){
    src('src/scss/app.scss', {sourcemaps: true})
        .pipe( sass({
            outputStyle: 'compressed'
        }).on('error',sass.logError))
        .pipe( dest('build/css', {sourcemaps:'.'}) )

    done()

    }

//Funcion para poder minimizar las imagenes grandes a tamaño requerido
export async function crop(done) {
    //ruta donde estan las imagenes
    const inputFolder = 'src/img/Imagenes_Inicio'
    //ruta donde se crea el folder
    const outputFolder = 'src/img/thumb';
    //medidas de la imagen reescalada
    const width = 250;
    const height = 180;
    if (!fs.existsSync(outputFolder)) {
        fs.mkdirSync(outputFolder, { recursive: true })
    }
    const images = fs.readdirSync(inputFolder).filter(file => {
        return /\.(jpg)$/i.test(path.extname(file));
    });
    try {
        images.forEach(file => {
            const inputFile = path.join(inputFolder, file)
            const outputFile = path.join(outputFolder, file)
            sharp(inputFile) 
                .resize(width, height, {
                    position: 'centre'
                })
                .toFile(outputFile)
        });

        done()
    } catch (error) {
        console.log(error)
    }
}

//Funciones para convertir las imagenes a webP
export async function imagenes(done) {
    //Ruta de inicio
    const srcDir = './src/img';
    const buildDir = './build/img';
    const images =  await glob('./src/img/**/*{jpg,png}')

    images.forEach(file => {
        const relativePath = path.relative(srcDir, path.dirname(file));
        const outputSubDir = path.join(buildDir, relativePath);
        procesarImagenes(file, outputSubDir);
    });
    done();
}

function procesarImagenes(file, outputSubDir) {
    if (!fs.existsSync(outputSubDir)) {
        fs.mkdirSync(outputSubDir, { recursive: true })
    }
    const baseName = path.basename(file, path.extname(file))
    const extName = path.extname(file)
    const outputFile = path.join(outputSubDir, `${baseName}${extName}`)
    const outputFileWebp = path.join(outputSubDir, `${baseName}.webp`)

    const options = { quality: 80 }
    sharp(file).jpeg(options).toFile(outputFile)
    sharp(file).webp(options).toFile(outputFileWebp)
}


//Funcion para mantener en la etapa de desarrollo el scss con el csss
//Es decir sincronizar las modificaciones de scss a css
export function dev(){
    //Modificar en tiempo real los css de scss
    watch('src/scss/**/*.scss', css)
    //Modificar en tiempo real los js de src a la carpeta de la app build
    watch('src/js/**/*.js', js)
    //Procesar todas las imagenes que estan en la carpeta img a webp
    watch('src/img/**/*.{jpg,png}', imagenes)
}

//Funcion para ejecutar todas las funciones para empezar a desarrollar, modificar

// no se añade el reescalamiento de imagenes con crop
export default series(js,css,imagenes,dev)