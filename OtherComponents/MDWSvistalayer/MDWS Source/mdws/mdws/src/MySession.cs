using System;
using System.Collections.Specialized;
using gov.va.medora.mdo;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdws.dto;
using System.Configuration;
using System.Xml;
using System.Reflection;
using System.IO;
using System.Web;
using gov.va.medora.mdws.conf;
using System.Collections.Generic;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdo.dao.vista;

namespace gov.va.medora.mdws
{
    [Serializable]
    public class MySession
    {
        // REST
        public string Token { get; set; }
        public DateTime LastUsed { get; set; }
        //Dictionary<string, AbstractConnection> _authCxns;
        //public Dictionary<string, AbstractConnection> AuthenticatedConnections 
        //{
        //    get 
        //    {
        //        if (_authCxns == null)
        //        {
        //            _authCxns = new Dictionary<string, AbstractConnection>();
        //        }
        //        return _authCxns;
        //    }
        //    set { _authCxns = value; }
        //}
        // END REST
        MdwsConfiguration _mdwsConfig;
        string _facadeName;
        SiteTable _siteTable;

        // set outside class
        ConnectionSet _cxnSet = new ConnectionSet();
        User _user;
        AbstractCredentials _credentials;
        string _defaultVisitMethod;
        public bool _excludeSite200;
        string _defaultPermissionString;
        AbstractPermission _primaryPermission;
        Patient _patient;
        //public ILog log;


        public MySession()
        {
            _mdwsConfig = new MdwsConfiguration();
            _defaultVisitMethod = _mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.DEFAULT_VISIT_METHOD];
            _defaultPermissionString = _mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.DEFAULT_CONTEXT];

            try
            {
                _siteTable = new mdo.SiteTable(_mdwsConfig.ResourcesPath + "xml\\" + _mdwsConfig.FacadeConfiguration.SitesFileName);
            }
            catch (Exception) { /* SiteTable is going to be null - how do we let the user know?? */ }
        }

        /// <summary>
        /// Every client application requesting a MDWS session (invokes a function with EnableSession = True attribute) passes
        /// through this point. Fetches facade configuration settings and sets up session for subsequent calls
        /// </summary>
        /// <param name="facadeName">The facade name being invoked (e.g. EmrSvc)</param>
        /// <exception cref="System.Configuration.ConfigurationErrorsException" />
        public MySession(string facadeName)
        {
            this._facadeName = facadeName;
            _mdwsConfig = new MdwsConfiguration(facadeName);
            _defaultVisitMethod = _mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.DEFAULT_VISIT_METHOD];
            _defaultPermissionString = _mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.DEFAULT_CONTEXT];
            _excludeSite200 = String.Equals("true", _mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.EXCLUDE_SITE_200], 
                StringComparison.CurrentCultureIgnoreCase);

            try
            {
                _siteTable = new mdo.SiteTable(_mdwsConfig.ResourcesPath + "xml\\" + _mdwsConfig.FacadeConfiguration.SitesFileName);
                watchSitesFile(_mdwsConfig.ResourcesPath + "xml\\");
            }
            catch (Exception) { /* SiteTable is going to be null - how do we let the user know?? */ }
        }

        /// <summary>
        /// Allow a client application to specifiy their sites file by name
        /// </summary>
        /// <param name="sitesFileName">The name of the sites file</param>
        /// <returns>SiteArray of parsed sites file</returns>
        public SiteArray setSites(string sitesFileName)
        {
            SiteArray result = new SiteArray();
            try
            {
                _siteTable = new mdo.SiteTable(_mdwsConfig.ResourcesPath + "xml\\" + sitesFileName);
                _mdwsConfig.FacadeConfiguration.SitesFileName = sitesFileName;
                watchSitesFile(_mdwsConfig.ResourcesPath + "xml\\");
                result = new SiteArray(_siteTable.Sites);
            }
            catch (Exception)
            {
                result.fault = new FaultTO("A sites file with that name does not exist on the server!");
            }
            return result;
        }

        #region Setters and Getters
        public MdwsConfiguration MdwsConfiguration
        {
            get { return _mdwsConfig; }
        }

        public string FacadeName
        {
            get { return _facadeName; }
        }

        public SiteTable SiteTable
        {
            get { return _siteTable; }
        }

        public ConnectionSet ConnectionSet
        {
            get { return _cxnSet ?? (_cxnSet = new ConnectionSet()); }
            set { _cxnSet = value; }
        }

        /// <summary>
        /// Call this function after all connections in ConnectionSet have been authenticated to set the session state for each of the connections
        /// </summary>
        internal void setAuthorized()
        {
            if (null == _cxnSet || 0 == _cxnSet.Count)
            {
                throw new ArgumentException("No connections!");
            }
            if (this.Sessions == null)
            {
                this.Sessions = new VistaStates();
            }
            foreach (string key in _cxnSet.Connections.Keys)
            {
                this.Sessions.setState(key, new VistaState(_cxnSet.Connections[key].getState()));
            }
        }

        /// <summary>
        /// Call this function after all connections in ConnectionSet have been authenticated to set the session state for each of the connections
        /// </summary>
        internal void setAuthorized(string id)
        {
            if (null == _cxnSet || 0 == _cxnSet.Count || null == _cxnSet.Connections || !_cxnSet.Connections.ContainsKey(id))
            {
                throw new ArgumentException("No connections!");
            }
            if (this.Sessions == null)
            {
                this.Sessions = new VistaStates();
            }
            
            this.Sessions.setState(id, new VistaState(_cxnSet.Connections[id].getState()));
        }

        public User User
        {
            get { return _user; }
            set { _user = value; }
        }

        public Patient Patient
        {
            get { return _patient; }
            set { _patient = value; }
        }

        public AbstractCredentials Credentials
        {
            get { return _credentials; }
            set { _credentials = value; }
        }

        public string DefaultPermissionString
        {
            get { return _defaultPermissionString; }
            set { _defaultPermissionString = value; }
        }

        public AbstractPermission PrimaryPermission
        {
            get { return _primaryPermission; }
            set 
            {
                _primaryPermission = value;
                _primaryPermission.IsPrimary = true;
            }
        }

        /// <summary>
        /// Defaults to MdwsConstants.BSE_CREDENTIALS_V2WEB if configuration key is not found in web.config
        /// </summary>
        public string DefaultVisitMethod
        {
            get { return String.IsNullOrEmpty(_defaultVisitMethod) ? MdwsConstants.BSE_CREDENTIALS_V2WEB : _defaultVisitMethod; }
            set { _defaultVisitMethod = value; }
        }
        #endregion

        public Object execute(string className, string methodName, object[] args)
        {
            string userIdStr = "";
            //if (_user != null)
            //{
            //    userIdStr = _user.LogonSiteId.Id + '/' + _user.Uid + ": ";
            //}
            string fullClassName = className;
            if (!fullClassName.StartsWith("gov."))
            {
                fullClassName = "gov.va.medora.mdws." + fullClassName;
            }
            Object theLib = Activator.CreateInstance(Type.GetType(fullClassName), new object[] { this });
            Type theClass = theLib.GetType();
            Type[] theParamTypes = new Type[args.Length];
            for (int i = 0; i < args.Length; i++)
            {
                theParamTypes[i] = args[i].GetType();
            }
            MethodInfo theMethod = theClass.GetMethod(methodName, theParamTypes);
            Object result = null;
            if (theMethod == null)
            {
                result = new Exception("Method " + className + " " + methodName + " does not exist.");
            }
            try
            {
                result = theMethod.Invoke(theLib, BindingFlags.InvokeMethod, null, args, null);
            }
            catch (Exception e)
            {
                if (e.GetType().IsAssignableFrom(typeof(System.Reflection.TargetInvocationException)) &&
                    e.InnerException != null)
                {
                    result = e.InnerException;
                }
                else
                {
                    result = e;
                }
                return result;
            }
            return result;
        }

        public bool HasBaseConnection
        {
            get
            {
                if (_cxnSet == null)
                {
                    return false;
                }
                return _cxnSet.HasBaseConnection;
            }
        }

        public void close()
        {
            _cxnSet.disconnectAll();
            _user = null;
            _patient = null;
        }


        void watchSitesFile(string path)
        {
            // This needs to be finished and tested before we implement - #2829
            //if (_mdwsConfig == null || _mdwsConfig.AllConfigs == null || !_mdwsConfig.AllConfigs.ContainsKey(MdwsConfigConstants.MDWS_CONFIG_SECTION)
            //    || !_mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION].ContainsKey(MdwsConfigConstants.WATCH_SITES_FILE))
            //{
            //    return;
            //}

            //bool watchFile = false;
            //Boolean.TryParse(_mdwsConfig.AllConfigs[MdwsConfigConstants.MDWS_CONFIG_SECTION][MdwsConfigConstants.WATCH_SITES_FILE], out watchFile);
            //if (!watchFile)
            //{
            //    return;
            //}

            FileSystemWatcher watcher = new FileSystemWatcher(path);
            watcher.Filter = (_mdwsConfig.FacadeConfiguration.SitesFileName);
            watcher.NotifyFilter = NotifyFilters.LastAccess | NotifyFilters.LastWrite | NotifyFilters.CreationTime
                | NotifyFilters.DirectoryName | NotifyFilters.FileName | NotifyFilters.Size;
            watcher.EnableRaisingEvents = true;
            watcher.Changed += new FileSystemEventHandler(watcher_Changed);
            watcher.Deleted += new FileSystemEventHandler(watcher_Changed);
            watcher.Created += new FileSystemEventHandler(watcher_Changed);
        }

        void watcher_Changed(object sender, FileSystemEventArgs e)
        {
            _siteTable = new SiteTable(_mdwsConfig.ResourcesPath + "xml\\" + _mdwsConfig.FacadeConfiguration.SitesFileName);
        }

        public bool hasExpired()
        {
            if (DateTime.Now.Subtract(this.LastUsed).CompareTo(this.MdwsConfiguration.TimeOut) > 0)
            {
                return true;
            }
            return false;
        }

        public AbstractStates Sessions { get; set; }
    }
}
