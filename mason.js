const mason = require('@joomlatools/mason-tools-v1');
const path = require('path');
const fs = require('fs').promises;

const frameworkFolder = process.cwd();
const libraryAssetsPath = `${frameworkFolder}/code/libraries/joomlatools/library/resources/assets`;
const koowaAssetsPath = `${frameworkFolder}/code/libraries/joomlatools/component/koowa/resources/assets`;
const KUIPath = `${path.resolve(frameworkFolder, '..')}/kodekit-ui/dist`;

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
            components: {
                files: {
                    repo: 'joomlatools/joomlatools-framework-files',
                },
                activities: {
                    repo: 'joomlatools/joomlatools-framework-activities',
                },
                scheduler: {
                    repo: 'joomlatools/joomlatools-framework-scheduler',
                },
                migrator: {
                    repo: 'joomlatools/joomlatools-framework-migrator',
                },
                tags: {
                    repo: 'joomlatools/joomlatools-framework-tags',
                },
            },
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

    if (buildConfig.includeComponents) {
        let promises = [];
        for (let [name, { repo, branch }] of Object.entries(buildConfig.components)) {
            promises.push(
                mason.github.download({
                    repo,
                    branch,
                    destination: `${frameworkCodeFolder}/libraries/joomlatools-components/${name}`,
                })
            );
        }

        await Promise.all(promises);
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
    await mason.fs.copyFolderContents(`${KUIPath}/fonts`,`${koowaAssetsPath}/fonts`);
    await mason.fs.copyFolderContents(`${KUIPath}/js`, `${libraryAssetsPath}/js`);
}

module.exports = {
    version: '1.0',
    tasks: {
        files,
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
