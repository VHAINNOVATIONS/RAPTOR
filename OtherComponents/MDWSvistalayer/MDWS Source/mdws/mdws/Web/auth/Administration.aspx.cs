using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Security.Principal;
using System.Reflection;
using gov.va.medora.mdws.dao.sql;
using gov.va.medora.mdws.conf;
using gov.va.medora.mdo.conf;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws.Web
{
    public partial class Administration : System.Web.UI.Page
    {
        bool _isInstallLaunch = false;

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!HttpContext.Current.User.IsInRole("Builtin\\Administrators"))
            {
                labelMessage.Text = "You must be a member of the server's Administrators group to view this page. " +
                    "(logged on as: " + HttpContext.Current.User.Identity.Name + ")";
                panelForm.Visible = false;
                return;
            }

            labelUser.Text = HttpContext.Current.User.Identity.Name;

            // the installer launches the admin page with a GET query string param to show as a launch from the installer
            if (Request.QueryString.Count > 0)
            {
                _isInstallLaunch = true;
            }

            if (!IsPostBack)
            {
                // TODO - check to see if this is a new deployment (i.e. no registry keys) - if so, create registry defaults
                if (_isInstallLaunch)
                {
                    labelMessage.Text = "Congratulations on your successful installation of MDWS! We set up each facade with " +
                    "some default settings for your convenience. These are usually sufficient and should not " + 
                    "require modification unless your needs dictate otherwise.";
                    //initializeConfiguration();
                }
                // get available log levels
                string[] logLevels = Enum.GetNames(typeof(ApplicationSessionsLogLevel));
                foreach (string s in logLevels)
                {
                    radioMdwsSessionsLogLevel.Items.Add(new ListItem(s, s));
                }
                radioMdwsSessionsLogLevel.SelectedIndex = 0;
                // set facade drop downs
                dropdownFacadeName.DataSource = MdwsUtils.getMdwsServices();
                dropdownFacadeName.DataTextField = "Name";
                dropdownFacadeName.DataValueField = "Name";
                dropdownFacadeName.DataBind();
                dropdownFacadeName.SelectedValue = "EmrSvc";
                setForm();
            }
        }

        private void initializeConfiguration()
        {
            IIdentity user = HttpContext.Current.User.Identity;
            WindowsIdentity wi = (WindowsIdentity)user;
            WindowsImpersonationContext impersonater = wi.Impersonate();

            try
            {
                MdwsConfiguration mdwsConfig = new MdwsConfiguration();

            }
            catch (Exception exc)
            {
                // problem writing values while impersonating
                labelMessage.Text = "MDWS encountered an error while configuring your default settings. This is usually " +
                    "because the host machine is not configured to enable impersonation by IIS. You can fix this by editing  " +
                    "your local security policy to allow the IUSR, IWAM and ASPNET user objects to impersonate. Go to Start > " +
                    "Settings > Control Panel > Administrative Tools > Local Security Policies > Expand Local Policies > " +
                    "User Rights Assignment > locate the 'Impersonate a Client After Authentication' setting > Open and add the " +
                    "user objects listed above to the group. Restart IIS and launch this page again. <br />(" + exc.Message + ")";
            }
            finally
            {
                impersonater.Undo();
            }
        }

        private void writeFacadeDefaults(string facadeName)
        {
        }

        protected void TestVistaSettingsClick(object sender, EventArgs e)
        {
            int port = 0;
            if (String.IsNullOrEmpty(textboxVistaIp.Text) || String.IsNullOrEmpty(textboxVistaPort.Text) ||
                !Int32.TryParse(textboxVistaPort.Text, out port))
            {
                labelMessage.Text = "Invalid Vista connection parameters. Please be sure to enter a valid IP address and port number";
                return;
            }

            DataSource testSrc = new DataSource();
            testSrc.Provider = textboxVistaIp.Text;
            testSrc.Modality = "HIS";
            testSrc.Port = port;
            testSrc.Protocol = "VISTA";
            testSrc.SiteId = new SiteId("900", "Test");

            string welcomeMsg = "";
            try
            {
                AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(testSrc.Protocol));
                AbstractConnection cxn = factory.getConnection(testSrc);
                cxn.connect();
                welcomeMsg = cxn.getWelcomeMessage();
                cxn.disconnect();
            }
            catch (Exception exc)
            {
                labelMessage.Text = "Poop. Unable to connect to that datasource. Please check your test system and try again." + 
                    "This might help figure out why:</p><p>" + exc.ToString() + "</p>";
                return;
            }

            labelMessage.Text = "<p>You rock. Connection successfully established. You should put this site in your VhaSites.xml " +
                "file is you'd like it to be available later on via MDWS.</p><p>" + welcomeMsg + "</p>";
        }

        protected void TestSqlSettingsClick(object sender, EventArgs e)
        {
            if (String.IsNullOrEmpty(textboxSqlDatabase.Text) || String.IsNullOrEmpty(textboxSqlServerPath.Text) ||
                String.IsNullOrEmpty(textboxSqlUsername.Text) || String.IsNullOrEmpty(textboxSqlPassword.Text))
            {
                labelMessage.Text = "You must complete all the SQL configuration fields to test your SQL connectivity";
                return;
            }

            AbstractSqlConfiguration sqlConfig = new MsSqlConfiguration();
            sqlConfig.Database = textboxSqlDatabase.Text;
            sqlConfig.Hostname = textboxSqlServerPath.Text;
            sqlConfig.Username = textboxSqlUsername.Text;
            sqlConfig.Password = textboxSqlPassword.Text;

            ConnectionTester dao = new ConnectionTester(sqlConfig.buildConnectionString());
            dto.BoolTO result = dao.canWrite();
            if (result.trueOrFalse)
            {
                labelMessage.Text = "You were able to write to your database with these SQL settings. Good job!";
            }
            else if (result.fault != null)
            {
                labelMessage.Text = "<p>Sad face. MDWS failed to write to your database with those SQL settings. Maybe the " +
                    "following error will give you a clue why:</p>" + result.fault.message;
            }
            else
            {
                labelMessage.Text = "MDWS couldn't successfully connect to your database with those parameters " +
                    "but no error was reported. You got yourself a real pickle there.";
            }
        }

        protected void SubmitChangesClick(object sender, EventArgs e)
        {
            IIdentity user = HttpContext.Current.User.Identity;
            WindowsIdentity wi = (WindowsIdentity)user;
            WindowsImpersonationContext impersonater = wi.Impersonate();

            try
            {
                Dictionary<string, Dictionary<string, string>> newSettings = new Dictionary<string, Dictionary<string, string>>();
                MdwsConfiguration mdwsConfig = new MdwsConfiguration();
                Dictionary<string, Dictionary<string, string>> oldSettings = mdwsConfig.AllConfigs;

                newSettings.Add(MdwsConfigConstants.MDWS_CONFIG_SECTION, new Dictionary<string, string>());
                newSettings.Add(MdwsConfigConstants.SQL_CONFIG_SECTION, new Dictionary<string, string>());
                newSettings.Add(dropdownFacadeName.Text, new Dictionary<string, string>());

                newSettings[MdwsConfigConstants.MDWS_CONFIG_SECTION].Add(MdwsConfigConstants.SESSIONS_LOG_LEVEL, radioMdwsSessionsLogLevel.SelectedValue);
                newSettings[MdwsConfigConstants.MDWS_CONFIG_SECTION].Add(MdwsConfigConstants.SESSIONS_LOGGING, radioMdwsSessionsLogging.SelectedValue);
                newSettings[MdwsConfigConstants.MDWS_CONFIG_SECTION].Add(MdwsConfigConstants.MDWS_PRODUCTION, radioButtonMdwsProduction.SelectedValue);

                newSettings[MdwsConfigConstants.SQL_CONFIG_SECTION].Add(MdwsConfigConstants.SQL_HOSTNAME, textboxSqlServerPath.Text);
                newSettings[MdwsConfigConstants.SQL_CONFIG_SECTION].Add(MdwsConfigConstants.SQL_DB, textboxSqlDatabase.Text);
                newSettings[MdwsConfigConstants.SQL_CONFIG_SECTION].Add(MdwsConfigConstants.SQL_USERNAME, textboxSqlUsername.Text);
                newSettings[MdwsConfigConstants.SQL_CONFIG_SECTION].Add(MdwsConfigConstants.SQL_PASSWORD, textboxSqlPassword.Text);

                newSettings[dropdownFacadeName.Text].Add(MdwsConfigConstants.FACADE_PRODUCTION, radioFacadeIsProduction.SelectedValue);
                if (String.IsNullOrEmpty(textboxFacadeSitesFileName.Text))
                {
                    newSettings[dropdownFacadeName.Text].Add(MdwsConfigConstants.FACADE_SITES_FILE, MdwsConstants.DEFAULT_SITES_FILE_NAME);
                }
                else
                {
                    newSettings[dropdownFacadeName.Text].Add(MdwsConfigConstants.FACADE_SITES_FILE, textboxFacadeSitesFileName.Text);
                }

                foreach (string key in newSettings.Keys)
                {
                    // if old settings doesn't have facade config, add it
                    if (!oldSettings.ContainsKey(key))
                    {
                        oldSettings.Add(key, new Dictionary<string,string>());
                    }
                    foreach (string settingKey in newSettings[key].Keys)
                    {
                        if (oldSettings[key].ContainsKey(settingKey))
                        {
                            oldSettings[key][settingKey] = newSettings[key][settingKey];
                        }
                        else
                        {
                            oldSettings[key].Add(settingKey, newSettings[key][settingKey]);
                        }
                    }
                }

                gov.va.medora.mdo.dao.file.ConfigFileDao fileDao = new mdo.dao.file.ConfigFileDao(mdwsConfig.ConfigFilePath);
                fileDao.writeToFile(oldSettings); // not really old settings any more 
            }
            catch (Exception exc)
            {
                labelMessage.Text = exc.ToString();
            }
            finally
            {
                impersonater.Undo();
            }
            setForm();
        }

        void setForm()
        {
            MdwsConfiguration mdwsConfig = new MdwsConfiguration(dropdownFacadeName.SelectedValue);

            radioButtonMdwsProduction.SelectedValue = mdwsConfig.IsProduction.ToString().ToLower();
            radioMdwsSessionsLogging.SelectedValue = mdwsConfig.ApplicationSessionsLogging.ToString().ToLower();
            radioMdwsSessionsLogLevel.SelectedValue = Enum.GetName(typeof(ApplicationSessionsLogLevel), mdwsConfig.ApplicationSessionsLogLevel);

            if (mdwsConfig.SqlConfiguration != null)
            {
                textboxSqlServerPath.Text = mdwsConfig.SqlConfiguration.Hostname;
                textboxSqlDatabase.Text = mdwsConfig.SqlConfiguration.Database;
                textboxSqlUsername.Text = mdwsConfig.SqlConfiguration.Username;
                textboxSqlPassword.Text = mdwsConfig.SqlConfiguration.Password;
            }

            if (mdwsConfig.FacadeConfiguration != null)
            {
                textboxFacadeSitesFileName.Text = mdwsConfig.FacadeConfiguration.SitesFileName;
                radioFacadeIsProduction.SelectedValue = mdwsConfig.FacadeConfiguration.IsProduction.ToString().ToLower();
                try
                {
                    Object currentFacade = Activator.CreateInstance(Type.GetType("gov.va.medora.mdws." + dropdownFacadeName.SelectedValue));
                    string facadeVersion = (string)Type.GetType("gov.va.medora.mdws." + dropdownFacadeName.SelectedValue).
                        GetField("VERSION").GetValue(dropdownFacadeName.SelectedValue);
                    textboxFacadeVersion.Text = facadeVersion;
                }
                catch (Exception)
                {
                    textboxFacadeVersion.Text = "0.0.0";
                }

            }
        }

        protected void ChangeFacade(Object sender, EventArgs e)
        {
            setForm();
        }

        protected void DownloadSitesFile_Click(object sender, EventArgs e)
        {
            MdwsConfiguration config = new MdwsConfiguration(dropdownFacadeName.SelectedValue);
            string connectionString = "";

            if (config != null && config.AllConfigs != null && config.AllConfigs.ContainsKey(MdwsConfigConstants.MDWS_CONFIG_SECTION)
                && config.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION].ContainsKey("UpdaterConnectionString"))
            {
                connectionString = config.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION]["UpdaterConnectionString"];
            }
            else
            {
                labelMessage.Text = "No updater connection information found in your config file!";
                return;
            }
            MdwsToolsDao dao = new MdwsToolsDao(connectionString);
            try
            {
                byte[] file = dao.getLatestSitesFile();
                if (file == null || file.Length == 0)
                {
                    labelMessage.Text = "Doesn't look to be a active sites file in the updater database. Please check back later.";
                }
                else
                {
                    Response.AddHeader("Content-Disposition", "attachment;filename=VhaSites.xml");
                    Response.ContentType = "application/octet-stream";
                    Response.BinaryWrite(file);
                    Response.Flush();
                    Response.End();
                }
            }
            catch (Exception exc)
            {
                labelMessage.Text = "Awww, snap. I couldn't get that for you. Maybe this will help figure out why:</p>" + exc.Message + "</p>";
            }
        }
    }
}
