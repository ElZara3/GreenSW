// ==================== IMPORTACIONES ====================
import path from 'path'
import fs from 'fs'
import { glob } from 'glob'
import { src, dest, watch, series, parallel } from 'gulp'
import * as dartSass from 'sass'
import gulpSass from 'gulp-sass'
import sharp from 'sharp'
import terser from 'gulp-terser'
import concat from 'gulp-concat'

const sass = gulpSass(dartSass)

// ==================== TAREA: JAVASCRIPT (Bundle único con mapa) ====================
export function js(done) {
    src([
        'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', // Bootstrap + Popper
        'src/js/**/*.js' // Tus scripts
    ], { sourcemaps: true }) // habilita los mapas
        .pipe(concat('bundle.js')) // combínalos todos
        .pipe(terser()) // minifica
        .pipe(dest('build/js', { sourcemaps: '.' })) // genera bundle.js + bundle.js.map
    done()
}

// ==================== TAREA: CSS ====================
export function css(done) {
    src('src/scss/app.scss', { sourcemaps: true })
        .pipe(
            sass({
                outputStyle: 'compressed',
                includePaths: [
                    'node_modules/bootstrap/scss' // permite @import "bootstrap"
                ]
            }).on('error', sass.logError)
        )
        .pipe(dest('build/css', { sourcemaps: '.' }))
    done()
}

// ==================== TAREA: REESCALAR IMÁGENES ====================
export async function crop(done) {
    const inputFolder = 'src/img/Imagenes_Inicio'
    const outputFolder = 'src/img/thumb'
    const width = 250
    const height = 180

    if (!fs.existsSync(outputFolder)) {
        fs.mkdirSync(outputFolder, { recursive: true })
    }

    const images = fs.readdirSync(inputFolder).filter(file => /\.(jpg)$/i.test(path.extname(file)))

    try {
        await Promise.all(
            images.map(file => {
                const inputFile = path.join(inputFolder, file)
                const outputFile = path.join(outputFolder, file)
                return sharp(inputFile)
                    .resize(width, height, { fit: 'cover' })
                    .toFile(outputFile)
            })
        )
        done()
    } catch (error) {
        console.error(error)
        done(error)
    }
}

// ==================== TAREA: CONVERTIR A WEBP ====================
export async function imagenes() {
    const srcDir = './src/img'
    const buildDir = 'build/img'
    const images = await glob('./src/img/**/*.{jpg,png}')

    return Promise.all(
        images.map(file => {
            const relativePath = path.relative(srcDir, path.dirname(file))
            const outputSubDir = path.join(buildDir, relativePath)
            return procesarImagenes(file, outputSubDir)
        })
    )
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
    return Promise.all([
        sharp(file).jpeg(options).toFile(outputFile),
        sharp(file).webp(options).toFile(outputFileWebp)
    ])
}

// ==================== WATCH / DESARROLLO ====================
export function dev() {
    watch('src/scss/**/*.scss', css)
    watch('src/js/**/*.js', js)
    watch('src/img/**/*.{jpg,png}', imagenes)
}

// ==================== TAREAS GLOBALES ====================
export default series(
    parallel(js, css, imagenes),
    dev
)
