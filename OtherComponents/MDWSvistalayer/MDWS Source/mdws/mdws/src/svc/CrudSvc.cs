using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Services;
using gov.va.medora.mdws.dto.vista.mgt;
using System.ServiceModel;
using System.ServiceModel.Web;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdo;
using gov.va.medora.mdo.domain.pool;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using System.ServiceModel.Activation;
using gov.va.medora.mdws.dto;
using System.IO;
using gov.va.medora.utils;

namespace gov.va.medora.mdws
{
    [AspNetCompatibilityRequirements(RequirementsMode = AspNetCompatibilityRequirementsMode.Allowed)]
    [ServiceBehavior(InstanceContextMode = InstanceContextMode.Single)]
    public class CrudSvc : ICrudSvc
    {
        MySession _mySession;

        public CrudSvc()
        {
            if (ConnectionPools.getInstance().PoolSource != null)
            {
                return; // already set up the pools!
            }

            _mySession = new MySession();
            SiteTable sites = _mySession.SiteTable;
            IList<AbstractPoolSource> sources = new List<AbstractPoolSource>();
            ConnectionPoolsSource poolsSource = new ConnectionPoolsSource();
            poolsSource.CxnSources = new Dictionary<string, ConnectionPoolSource>();
            User user = _mySession.MdwsConfiguration.ApplicationProxy;
            AbstractCredentials creds = new VistaCredentials();
            creds.AccountName = user.UserName;
            creds.AccountPassword = user.Pwd;
            creds.AuthenticationSource = new DataSource(); // BSE
            creds.AuthenticationSource.SiteId = new SiteId(user.LogonSiteId.Id, user.LogonSiteId.Name);
            creds.AuthenticationToken = user.LogonSiteId.Id + "_" + user.Uid;
            creds.LocalUid = user.Uid;
            creds.FederatedUid = user.SSN.toString();
            creds.SubjectName = user.Name.getLastNameFirst();
            creds.SubjectPhone = user.Phone;
            creds.SecurityPhrase = _mySession.MdwsConfiguration.AllConfigs
                [conf.MdwsConfigConstants.MDWS_CONFIG_SECTION][conf.MdwsConfigConstants.SECURITY_PHRASE];

            foreach (DataSource source in sites.Sources)
            {
                if (!String.Equals(source.Protocol, "VISTA", StringComparison.CurrentCultureIgnoreCase))
                {
                    continue;
                }
                ConnectionPoolSource newSource = new ConnectionPoolSource()
                {
                    LoadStrategy = (LoadingStrategy)Enum.Parse(typeof(LoadingStrategy), 
                        _mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_LOAD_STRATEGY]),
                    MaxPoolSize = Convert.ToInt32(_mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_MAX_CXNS]),
                    MinPoolSize = Convert.ToInt32(_mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_MIN_CXNS]),
                    PoolExpansionSize = Convert.ToInt32(_mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_EXPAND_SIZE]),
                    WaitTime = TimeSpan.Parse(_mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_WAIT_TIME]),
                    Timeout = TimeSpan.Parse(_mySession.MdwsConfiguration.AllConfigs[conf.MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][conf.MdwsConfigConstants.CONNECTION_POOL_CXN_TIMEOUT]),
                    CxnSource = source,
                    Credentials = creds
                };
                newSource.CxnSource.Protocol = "PVISTA";
                poolsSource.CxnSources.Add(source.SiteId.Id, newSource);
            }

            ConnectionPools pools = (ConnectionPools)AbstractResourcePoolFactory.getResourcePool(poolsSource);
        }

        public Stream create(System.IO.Stream postBody, String siteId, String pwd)
        {
            if (!String.Equals(pwd, _mySession.MdwsConfiguration.AllConfigs["CRUD"]["Password"]))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(new TextTO() { fault = new FaultTO("Incorrect password") })));
            }
            if (!String.Equals("true", _mySession.MdwsConfiguration.AllConfigs["CRUD"]["EnableCreate"], StringComparison.CurrentCultureIgnoreCase))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(new TextTO() { fault = new FaultTO("Create not enabled") })));
            }

            MySession newSession = new MySession();
            VistaRecordTO deserializedBody = null;
            try
            {
                String body = "";
                using (StreamReader sr = new StreamReader(postBody))
                {
                    body = sr.ReadToEnd();
                }
                deserializedBody = JsonUtils.Deserialize<VistaRecordTO>(body);
            }
            catch (Exception exc)
            {
                TextTO fault = new TextTO() { fault = new FaultTO(exc) };
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(fault)));
            }

            TextTO result = (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<VistaRecordTO, TextTO>(new ToolsLib(newSession).create),
                new object[] { deserializedBody });

            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(result)));
        }

        public Stream readRange(string siteId, string file, string fields, string iens, string flags, string from, string xref, string maxRex, string part, string screen, string identifier, String pwd)
        {
            if (!String.Equals(pwd, _mySession.MdwsConfiguration.AllConfigs["CRUD"]["Password"]))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextArray>(new TextArray() { fault = new FaultTO("Incorrect password") })));
            }
            if (!String.Equals("true", _mySession.MdwsConfiguration.AllConfigs["CRUD"]["EnableReadRange"], StringComparison.CurrentCultureIgnoreCase))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextArray>(new TextArray() { fault = new FaultTO("Read range not enabled") })));
            }

            MySession newSession = new MySession();
            TextArray result = (TextArray)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, String, String, String, String, String, String, String, String, TextArray>(new ToolsLib(newSession).readRange),
                new object[] { file, fields, iens, flags, xref, maxRex, from, part, screen, identifier });
            
            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextArray>(result)));
        }
        public Stream readAll(String siteId, String file, String recordId, String pwd)
        {
            return read(siteId, file, recordId, "*", pwd);
        }

        public Stream read(String siteId, String file, String recordId, String fields, String pwd)
        {
            if (!String.Equals(pwd, _mySession.MdwsConfiguration.AllConfigs["CRUD"]["Password"]))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaRecordTO>(new VistaRecordTO() { fault = new FaultTO("Incorrect password") })));
            }
            if (!String.Equals("true", _mySession.MdwsConfiguration.AllConfigs["CRUD"]["EnableRead"], StringComparison.CurrentCultureIgnoreCase))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaRecordTO>(new VistaRecordTO() { fault = new FaultTO("Read not enabled") })));
            }

            MySession newSession = new MySession();
            VistaRecordTO result = (VistaRecordTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, String, VistaRecordTO>(new ToolsLib(newSession).read),
                new object[] { recordId, fields, file });

            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaRecordTO>(result)));
        }

        public Stream update(Stream postBody, String siteId, String pwd)
        {
            if (!String.Equals(pwd, _mySession.MdwsConfiguration.AllConfigs["CRUD"]["Password"]))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaRecordTO>(new VistaRecordTO() { fault = new FaultTO("Incorrect password") })));
            }
            if (!String.Equals("true", _mySession.MdwsConfiguration.AllConfigs["CRUD"]["EnableUpdate"], StringComparison.CurrentCultureIgnoreCase))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaRecordTO>(new VistaRecordTO() { fault = new FaultTO("Update not enabled") })));
            }

            MySession newSession = new MySession();
            VistaRecordTO deserializedBody = null;
            try
            {
                String body = "";
                using (StreamReader sr = new StreamReader(postBody))
                {
                    body = sr.ReadToEnd();
                }
                deserializedBody = JsonUtils.Deserialize<VistaRecordTO>(body);
            }
            catch (Exception exc)
            {
                TextTO fault = new TextTO() { fault = new FaultTO(exc) };
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(fault)));

            }

            TextTO result = (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func< VistaRecordTO, TextTO>(new ToolsLib(newSession).update),
                new object[] { deserializedBody });

            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(result)));

        }

        public Stream delete(String siteId, String file, String recordId, String pwd)
        {
            if (!String.Equals(pwd, _mySession.MdwsConfiguration.AllConfigs["CRUD"]["Password"]))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(new TextTO() { fault = new FaultTO("Incorrect password") })));
            }
            if (!String.Equals("true", _mySession.MdwsConfiguration.AllConfigs["CRUD"]["EnableDelete"], StringComparison.CurrentCultureIgnoreCase))
            {
                return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(new TextTO() { fault = new FaultTO("Delete not enabled") })));
            }

            MySession newSession = new MySession();
            TextTO result = (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, TextTO>(new ToolsLib(newSession).delete),
                new object[] { recordId, file });
            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<TextTO>(result)));
        }

        public Stream getVistaFile(String siteId, String fileNumber, String pwd)
        {
            MySession newSession = new MySession();
            VistaFileTO result = (VistaFileTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute(newSession,
                new Func<String, bool, VistaFileTO>(new ToolsLib(newSession).getFile),
                new object[] { fileNumber, true });
            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<VistaFileTO>(result)));
        }

        public Stream helloWorld(String yourName)
        {
            return new MemoryStream(System.Text.Encoding.UTF8.GetBytes(JsonUtils.Serialize<DdrRpcResult>(new DdrRpcResult() { result = new String[] { "Hello " + yourName } })));
        }
    }

    [ServiceContract(Namespace = "http://mdws.medora.va.gov/CrudSvc")]
    interface ICrudSvc
    {
        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}/{file}?fields={fields}&iens={iens}&flags={flags}&from={from}&xref={xref}&maxRex={maxRex}&part={part}&screen={screen}&identifier={identifier}&pwd={pwd}")]
        Stream readRange(string siteId, string file, string fields, string iens, string flags, string from, string xref, string maxRex, string part, string screen, string identifier, String pwd);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}/{file}/{recordId}/{fields}?pwd={pwd}")]
        Stream read(string siteId, string file, string recordId, string fields, String pwd);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}/{file}/{recordId}?pwd={pwd}")]
        Stream readAll(string siteId, string file, string recordId, String pwd);

        [OperationContract]
        [WebInvoke(Method = "POST", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}?pwd={pwd}")]
        Stream create(System.IO.Stream postBody, String siteId, String pwd);

        [OperationContract]
        [WebInvoke(Method = "PUT", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}?pwd={pwd}")]
        Stream update(System.IO.Stream postBody, String siteId, String pwd);

        [OperationContract]
        [WebInvoke(Method = "DELETE", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}/{file}/{recordId}?pwd={pwd}")]
        Stream delete(string siteId, string file, string recordId, String pwd);

        //[OperationContract]
        //[WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "{siteId}?file={file}&iens={iens}&fields={fields}&flags={flags}&maxRex={maxRex}&from={from}&part={part}&xref={xref}&screen={screen}&identifier={identifier}")]
        //Stream selectPlus(string siteId, string file, string iens, string fields, string flags, string maxRex, string from, string part, string xref, string screen, string identifier);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "hello/{yourName}")]
        Stream helloWorld(String yourName);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "dd/{siteId}/{fileNumber}?pwd={pwd}")]
        Stream getVistaFile(String siteId, String fileNumber, String pwd);
    }
}