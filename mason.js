const mason = require('@joomlatools/mason-tools-v1');
const path = require('path');
const fs = require('fs').promises;

const frameworkFolder = process.cwd();
const libraryAssetsPath = `${frameworkFolder}/code/libraries/joomlatools/library/resources/assets`;
const koowaAssetsPath = `${frameworkFolder}/code/libraries/joomlatools/component/koowa/resources/assets`;
const KUIPath = `${path.resolve(frameworkFolder, '../..')}/tools/kodekit-ui/dist`;

const filesPath = `${frameworkFolder}/code/libraries/joomlatools-components/files/resources/assets`;

async function filesCss() {
    await Promise.all([
        mason.sass.compileFolder(`${filesPath}/scss`),
        mason.sass.minifyFolder(`${filesPath}/scss`),
    ]);
}

async function filesJs() {
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

async function build({ config = {} }) {
    const buildConfig = mason.config.merge(
        {
            source: 'local',
            location: frameworkFolder,
            appendVersion: false,
            destination: `${frameworkFolder}/joomlatools-framework.zip`,
            compress: true,
            githubToken: null,
            branch: 'master',
            includeComponents: true,
        },
        config
    );

    const { path: tmp, cleanup } = await mason.fs.getTemporaryDirectory();

    const framework = `${tmp}/framework`;
    const frameworkCodeFolder = `${framework}/code`;

    mason.log.debug(`Using ${tmp} folder for framework build`);

    if (buildConfig.source !== 'remote' && buildConfig.location) {
        await mason.fs.copyWithoutHiddenFiles(buildConfig.location, framework);
    } else {
        await mason.github.download({
            repo: 'joomlatools/joomlatools-framework',
            branch: buildConfig.branch,
            destination: framework,
        });
    }

    if (!buildConfig.includeComponents) {
        await fs.rm(`${frameworkCodeFolder}/libraries/joomlatools-components/`, { recursive: true, force: true })
        await mason.fs.ensureDir(`${frameworkCodeFolder}/libraries/joomlatools-components/`)
    }

    await fs.copyFile(`${framework}/LICENSE.txt`, `${frameworkCodeFolder}/LICENSE`);
    await fs.copyFile(
        `${frameworkCodeFolder}/plugins/system/joomlatools/joomlatools.xml`,
        `${frameworkCodeFolder}/joomlatools.xml`
    );
    await fs.copyFile(
        `${frameworkCodeFolder}/plugins/system/joomlatools/script.php`,
        `${frameworkCodeFolder}/script.php`
    );

    if (buildConfig.appendVersion) {
        const versionFile = (await fs.readFile(`${frameworkCodeFolder}/libraries/joomlatools/library/koowa.php`)).toString();
        const version = versionFile.match(/VERSION\s+=\s+'(.*?)'/);

        if (version) {
            if (buildConfig.destination.includes('.zip')) {
                buildConfig.destination = buildConfig.destination.replace('.zip', `-${version[1]}.zip`);
            } else {
                buildConfig.destination += `-${version[1]}`;
            }
        }
    }

    if (buildConfig.compress) {
        await mason.fs.archiveDirectory(frameworkCodeFolder, buildConfig.destination);
    } else {
        await mason.fs.copy(frameworkCodeFolder, buildConfig.destination);
    }

    await cleanup();
}

async function css() {
    await Promise.all([
        mason.sass.compileFolder(`${koowaAssetsPath}/scss`),
        mason.sass.minifyFolder(`${koowaAssetsPath}/scss`),
    ]);
}

async function files() {
    await mason.fs.copyFolderContents(`${KUIPath}/css`, `${libraryAssetsPath}/css`);
    await mason.fs.copyFolderContents(`${KUIPath}/fonts`, `${libraryAssetsPath}/fonts`);
    await mason.fs.copyFolderContents(`${KUIPath}/js`, `${libraryAssetsPath}/js`);
}

module.exports = {
    version: '1.0',
    tasks: {
        files,
        filesCss,
        filesJs,
        css,
        build,
        watch: {
            path: [`${libraryAssetsPath}/scss`, `${koowaAssetsPath}/scss`],
            callback: async (path) => {
                if (path.endsWith('.scss')) {
                    await css();
                }
            },
        },
        default: ['files', 'css'],
    },
};
