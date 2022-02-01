
const mason = require('@joomlatools/mason-tools-v1');

const filesPath = `${process.cwd()}/resources/assets`;

const watchPath = [`${filesPath}/scss/**/*.scss`, `${filesPath}/js/**/*.js`];

async function css() {
    await Promise.all([
        mason.sass.compileFolder(`${filesPath}/scss`),
        mason.sass.minifyFolder(`${filesPath}/scss`),
    ]);
}

async function js() {
    const jsMap = {
        [`${filesPath}/js/files.js`]: [
            `${filesPath}/js/src/history.js`,
            `${filesPath}/js/src/ejs.js`,
            `${filesPath}/js/src/spin.min.js`,
            `${filesPath}/js/src/files.utilities.js`,
            `${filesPath}/js/src/files.state.js`,
            `${filesPath}/js/src/files.template.js`,
            `${filesPath}/js/src/files.grid.js`,
            `${filesPath}/js/src/files.tree.js`,
            `${filesPath}/js/src/files.row.js`,
            `${filesPath}/js/src/files.paginator.js`,
            `${filesPath}/js/src/files.pathway.js`,
            `${filesPath}/js/src/files.app.js`,
            `${filesPath}/js/src/files.compact.js`,
            `${filesPath}/js/src/files.attachments.app.js`,
            `${filesPath}/js/src/files.uploader.js`,
            `${filesPath}/js/src/files.copymove.js`
        ],
        [`${filesPath}/js/files.select.js`]: [
            `${filesPath}/js/src/files.select.js`,
        ],
        [`${filesPath}/js/ejs_utilities.js`]: [
            `${filesPath}/js/src/ejs.js`,
            `${filesPath}/js/src/files.utilities.js`,
        ],
        [`${filesPath}/js/uploader.js`]: [
            `${filesPath}/js/src/uploader/plupload.full.min.js`,
            `${filesPath}/js/src/uploader/jquery-ui.js`,
            `${filesPath}/js/src/uploader/dot.js`,
            `${filesPath}/js/src/uploader/koowa.uploader.js`,
            `${filesPath}/js/src/uploader/koowa.uploader.overwritable.js`,
        ],
        [`${filesPath}/js/attachments.js`]: [
            `${filesPath}/js/src/ejs.js`,
            `${filesPath}/js/src/files.attachments.js`,
        ],
        [`${filesPath}/js/plyr.js`]: [
            `${filesPath}/js/src/plyr.js`,
            `${filesPath}/js/src/files.plyr.js`,
        ],
        [`${filesPath}/js/mootools.js`]: [
            `${filesPath}/js/src/mootools-core.js`,
            `${filesPath}/js/src/mootools-more.js`,
        ],
    }

    for (let [target, sourcesFiles] of Object.entries(jsMap)) {
        await mason.fs.concat(sourcesFiles, target);
        await mason.js.minify(target, target);
    }
}

module.exports = {
    version: '1.0',
    tasks: {
        js,
        css,
        watch: {
            path: [watchPath],
            callback: async (path) => {
                if (path.endsWith('.scss')) {
                    await css();
                } else if (path.endsWith('.scss')) {
                    await css();
                }
            },
        },
        default: ['js', 'css'],
    },
};
