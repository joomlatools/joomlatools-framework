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

    // Guard: the bundled third-party libraries must be scoped under the
    // Joomlatools\ namespace before they are packed, otherwise they collide with
    // the host CMS's own copies at runtime (see build/scope-vendor.sh). Runs for
    // `mason build` here AND every extension `mason bundle` (which delegates to
    // this task via buildFramework -> frameworkMason.tasks.build).
    const libDir = `${frameworkCodeFolder}/libraries/joomlatools`;
    const fix = 'Run "composer scope-vendor" in code/libraries/joomlatools before building.';

    // (1) Correctness: every class in the generated classmap must live under
    //     Joomlatools\ (Composer\ is composer's own runtime infrastructure).
    //     Package-agnostic — needs no per-library knowledge.
    const classmapFile = `${libDir}/vendor/composer/autoload_classmap.php`;
    let classmap;
    try {
        classmap = (await fs.readFile(classmapFile)).toString();
    } catch (e) {
        throw new Error(`Scoped vendor autoloader missing (${classmapFile}). ${fix}`);
    }

    const mappedClasses = [...classmap.matchAll(/^\s*'([^']+)'\s*=>/gm)].map((m) => m[1]);
    const unscoped = mappedClasses.filter((c) => !c.startsWith('Joomlatools') && !c.startsWith('Composer'));

    if (mappedClasses.length === 0 || unscoped.length > 0) {
        const detail = mappedClasses.length === 0 ? 'classmap is empty' : `unscoped: ${unscoped.slice(0, 5).join(', ')}`;
        throw new Error(`Bundled libraries are not scoped under Joomlatools\\ (${detail}). ${fix}`);
    }

    // (2) Completeness: every library declared in composer.json "require" must
    //     actually contribute classes to the scoped autoloader. The classmap maps
    //     each class to a file path that contains the package's directory (e.g.
    //     ".../imagine/imagine/lib/..."), so we assert the classmap references
    //     each required package. Combined with (1) — every mapped class is under
    //     Joomlatools\ — this proves the library was scoped AND bundled, not merely
    //     that a folder exists (an unscoped or stale folder would fail (1), and a
    //     not-regenerated classmap fails here). Catches a dependency added to
    //     "require" without re-running `composer scope-vendor`. Platform
    //     requirements (php, ext-*) have no "/" and are skipped.
    let manifest;
    try {
        manifest = JSON.parse((await fs.readFile(`${libDir}/composer.json`)).toString());
    } catch (e) {
        throw new Error(`Cannot read ${libDir}/composer.json. ${fix}`);
    }

    const requiredPackages = Object.keys(manifest.require || {}).filter((name) => name.includes('/'));
    const missing = requiredPackages.filter((name) => !classmap.includes(`/${name}/`));

    if (missing.length > 0) {
        throw new Error(`Required libraries not present in the scoped autoloader (${missing.join(', ')}). ${fix}`);
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
