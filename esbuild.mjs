import * as esbuild from 'esbuild'
import notifier from 'node-notifier';
import { formatISO9075 } from 'date-fns';

const isProduction = process.env.NODE_ENV === 'production';
const args = process.argv.slice(2);
const watch = args.includes('--watch');

const watchPlugin = {
    name: 'watch-plugin',
    setup({onStart, onEnd, ...rest}){
        const term = watch ? 'watch build' : 'Build';

        onStart( () => {
            const date = formatISO9075(new Date());
            // console.log('\x1b[33m%s\x1b[0m', `${term} started: ${new Date(new Date().toJSON())}`)
            // console.log(`\x1b[39m[${new Date(new Date().toJSON())}]\x1b[0m`);
            console.log(`\u001B[2m\u001B[90m[${date}]\u001B[39m\u001B[22m \x1b[33m${term} started...\x1b[0m`);
        })

        onEnd( result => {

            const date = formatISO9075(new Date());

            if( result.errors.length ) {
                // console.error('\x1b[31m%s\x1b[0m', `${term} failed: ${new Date(new Date().toJSON())}`, result.errors);
                console.log(`\u001B[2m\u001B[90m[${date}]\u001B[39m\u001B[22m \x1b[31m${term} failed!\x1b[0m`, result.errors);

                notifier.notify({
                    title: `${term} Error!`,
                    message: result.errors.map( err => `${err.text} \n`)
                });
            } else {

                // console.log("rest", rest);
                // console.log('\x1b[32m%s\x1b[0m',`${term} succeeded: ${new Date(new Date().toJSON())}`);
                console.log(`\u001B[2m\u001B[90m[${date}]\u001B[39m\u001B[22m \x1b[32m${term} succeeded.\x1b[0m`);

            }
        })
    }
}

const ctx = await esbuild.context({
    color: true,
    entryPoints: [
        'javascript/notice.js',
    ],
    entryNames: '[dir]/[name]-bundle',
    bundle: true,
    outbase: 'javascript',
    outdir: 'js',
    minify: isProduction, // Minify only in production
    sourcemap: !isProduction, // Source maps only in development
    treeShaking: true,
    // pure: ['console.log'],
    loader: {
        '.jpg': 'dataurl',
        '.png': 'dataurl',
        '.svg': 'text',
        '.js': 'jsx'
    },
    plugins:[
        watchPlugin
    ]
})

if( watch ) {
    await ctx.watch();
    console.log('\x1b[36m%s\x1b[0m', 'watching... (╭ರ_•́) 👀');
} else {
    ctx.rebuild();
    ctx.dispose();
}