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

namespace gov.va.medora.mdws
{
    [AspNetCompatibilityRequirements(RequirementsMode = AspNetCompatibilityRequirementsMode.Allowed)]
    [ServiceBehavior(InstanceContextMode = InstanceContextMode.Single)]
    public class RestQuerySvc : IRestQuerySvc
    {
        MySession _mySession;

        public RestQuerySvc()
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

        //public TextTO create(String siteId, String file, String parentRecordIdString, String jsonDictionaryFieldsAndValues)
        //{
        //    MySession newSession = new MySession();
        //    return (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
        //        .execute(newSession, new Func<String, String, String, TextTO>(new ToolsLib(newSession).create),
        //        new object[] { jsonDictionaryFieldsAndValues, file, parentRecordIdString });
        //}

        public VistaRecordTO read(String siteId, String file, String recordId, String fields)
        {
            MySession newSession = new MySession();
            return (VistaRecordTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, String, VistaRecordTO>(new ToolsLib(newSession).read),
                new object[] { recordId, fields, file });
        }

        public TextTO update(String siteId, String file, String recordId, String jsonDictionaryFieldsAndValues)
        {
            MySession newSession = new MySession();
            return (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, String, TextTO>(new ToolsLib(newSession).update),
                new object[] { jsonDictionaryFieldsAndValues, recordId, file });
        }

        public TextTO delete(String siteId, String file, String recordId)
        {
            MySession newSession = new MySession();
            return (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId)
                .execute(newSession, new Func<String, String, TextTO>(new ToolsLib(newSession).delete),
                new object[] { recordId, file });
        }

        public VistaFileTO getFile(string siteId, string fileNumber, bool includeXRefs)
        {
            MySession newSession = new MySession();
            return (VistaFileTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, bool, VistaFileTO>(new ToolsLib(newSession).getFile), new object[] { fileNumber, includeXRefs });
        }

        public XRefArray getXRefs(string siteId, string fileNumber)
        {
            MySession newSession = new MySession();
            return (XRefArray)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, XRefArray>(new ToolsLib(newSession).getXRefs), new object[] { fileNumber });
        }

        [WebMethod(EnableSession = true)]
        public TextTO getVariableValue(string siteId, string arg)
        {
            MySession newSession = new MySession();
            return (TextTO)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, TextTO>(new ToolsLib(newSession).getVariableValue), new object[] { arg });
        }

        public TextArray ddrLister(string siteId, string file, string iens, string fields, string flags, string maxrex, string from, string part, string xref, string screen, string identifier)
        {
            MySession newSession = new MySession();
            return (TextArray)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, string, string, string, string, string, string, string, string, string, TextArray>(new ToolsLib(newSession).ddrLister),
                new object[] { file, iens, fields, flags, maxrex, from, part, xref, screen, identifier });
        }
        public TextArray ddrGetsEntry(string siteId, string file, string iens, string flds, string flags)
        {
            MySession newSession = new MySession();
            return (TextArray)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, string, string, string, TextArray>(new ToolsLib(newSession).ddrGetsEntry), new object[] { file, iens, flds, flags });
        }

        public TaggedText siteHasPatch(string siteId, string patchId)
        {
            MySession newSession = new MySession();
            return (TaggedText)QueryTemplate.getQuery(QueryType.STATELESS, siteId).execute
                (newSession, new Func<string, TaggedText>(new ConnectionLib(newSession).siteHasPatch), new object[] { patchId });
        }
    }

    [ServiceContract(Namespace = "http://mdws.medora.va.gov/RestQuerySvc")]
    interface IRestQuerySvc
    {
        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "record/{siteId}/{file}/{recordId}/{fields}")]
        VistaRecordTO read(string siteId, string file, string recordId, string fields);

        //[OperationContract]
        //[WebInvoke(Method = "POST", ResponseFormat = WebMessageFormat.Json, UriTemplate = "record/{siteId}/{file}/{parentRecordId}/{values}")]
        //TextTO create(string siteId, string file, string parentRecordId, string values);

        [OperationContract]
        [WebInvoke(Method = "PUT", ResponseFormat = WebMessageFormat.Json, UriTemplate = "record/{siteId}/{file}/{recordId}/{values}")]
        TextTO update(string siteId, string file, string recordId, string values);

        [OperationContract]
        [WebInvoke(Method = "DELETE", ResponseFormat = WebMessageFormat.Json, UriTemplate = "record/{siteId}/{file}/{recordId}")]
        TextTO delete(string siteId, string file, string recordId);


        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "getFile?siteId={siteId}&fileNumber={fileNumber}&includeXRefs={includeXRefs}")]
        VistaFileTO getFile(string siteId, string fileNumber, bool includeXRefs);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "getXRefs?siteId={siteId}&fileNumber={fileNumber}")]
        XRefArray getXRefs(string siteId, string fileNumber);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "ddrGetsEntry?siteId={siteId}&file={file}&iens={iens}&flds={flds}&flags={flags}")]
        TextArray ddrGetsEntry(string siteId, string file, string iens, string flds, string flags);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "ddrLister?siteId={siteId}&file={file}&iens={iens}&fields={fields}&flags={flags}&maxrex={maxrex}&from={from}&part={part}&xref={xref}&screen={screen}&identifier={identifier}")]
        TextArray ddrLister(string siteId, string file, string iens, string fields, string flags, string maxrex, string from, string part, string xref, string screen, string identifier);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "gvv?siteId={siteId}&arg={arg}")]
        TextTO getVariableValue(string siteId, string arg);

        [OperationContract]
        [WebInvoke(Method = "GET", ResponseFormat = WebMessageFormat.Json, UriTemplate = "siteHasPatch?siteId={siteId}&patchId={patchId}")]
        TaggedText siteHasPatch(string siteId, string patchId);
    }
}