using System;
using System.Web;
using System.Collections;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text.RegularExpressions;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.api;
using gov.va.medora.utils;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class ConnectionLib
    {
        MySession mySession;
        AbstractConnection myCxn;

        internal const string NO_CONNECTIONS = "There are no open connections";
        internal const string ALREADY_CONNECTED_TO_SITE = "You are already connected to that site";
        internal const string SITE_NOT_IN_SITE_TABLE = "Site not in site table";
        internal const string NO_SITECODE = "Missing sitecode";
        internal const string NO_DUZ = "Missing DUZ";
        internal const string NO_DFN = "Missing DFN";
        internal const string NO_USER_NAME = "Missing User Name";
        internal const string NO_SSN = "Missing SSN";
        internal const string NO_MPI_PID = "Missing MPI Patient ID";
        internal const string NO_SECURITY_PHRASE = "Missing Security Phrase";
        internal const string NO_SITE_TABLE = "MDWS can't load your sites table";
        internal const string NO_LOGGED_IN_USER = "No logged in user";
        internal const string NO_PATIENT = "No patient has been selected";

        public ConnectionLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public DataSourceArray connectToLoginSite(string sitecode)
        {
            DataSourceArray result = new DataSourceArray();
            if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO(NO_SITECODE);
            }
            else if (mySession.SiteTable == null)
            {
                result.fault = new FaultTO(NO_SITE_TABLE);
            }
            else if (mySession.SiteTable.getSite(sitecode) == null)
            {
                result.fault = new FaultTO(SITE_NOT_IN_SITE_TABLE);
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0 && mySession.ConnectionSet.HasConnection(sitecode))
            {
                result.fault = new FaultTO(ALREADY_CONNECTED_TO_SITE);
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Site site = mySession.SiteTable.getSite(sitecode);
                DataSource src = site.getDataSourceByModality("HIS");
                AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));
                AbstractConnection c = factory.getConnection(src);
                c.connect();
                result = new DataSourceArray(src);
                result.items[0].welcomeMessage = c.getWelcomeMessage();
                mySession.ConnectionSet.Add(c);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public DataSourceTO connectSite(string sitecode)
        {
            DataSourceTO result = new DataSourceTO();
            if (String.IsNullOrEmpty(sitecode))
            {
                result.fault = new FaultTO(NO_SITECODE);
            }
            else if (mySession.SiteTable == null || mySession.SiteTable.getSite(sitecode) == null)
            {
                result.fault = new FaultTO(NO_SITE_TABLE);
            }
            else if (mySession.ConnectionSet != null && mySession.ConnectionSet.Count > 0 && mySession.ConnectionSet.HasConnection(sitecode))
            {
                result.fault = new FaultTO(ALREADY_CONNECTED_TO_SITE);
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Site site = (Site)mySession.SiteTable.Sites[sitecode];
                DataSource dataSource = site.getDataSourceByModality("HIS");
                AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(dataSource.Protocol));
                AbstractConnection c = factory.getConnection(dataSource);
                c.connect();
                result = new DataSourceTO(dataSource);
                result.welcomeMessage = c.getWelcomeMessage();
                mySession.ConnectionSet.Add(c);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }


        public TaggedTextArray disconnectAll()
        {
            TaggedTextArray result = new TaggedTextArray();
            if (mySession.ConnectionSet == null || mySession.ConnectionSet.Count == 0)
            {
                result.fault = new FaultTO(NO_CONNECTIONS);
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                IndexedHashtable t = mySession.ConnectionSet.disconnectAll();
                result = new TaggedTextArray(t);
                mySession.ConnectionSet.Clear();
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            
            return result;
        }


        public TextTO disconnectSite()
        {
            TextTO result = new TextTO();
            if (mySession.ConnectionSet == null || mySession.ConnectionSet.Count == 0)
            {
                result.fault = new FaultTO(NO_CONNECTIONS);
                return result;
            }
            try
            {
                mySession.ConnectionSet.disconnectAll();
                result.text = "OK";
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        // This is still in ToolsService - need to get rid of it
        public TaggedTextArray disconnectSites()
        {
            return disconnectAll();
        }

        public TaggedTextArray disconnectRemoteSites()
        {
            TaggedTextArray result = new TaggedTextArray();
            if (mySession.ConnectionSet == null || mySession.ConnectionSet.Count == 0)
            {
                result.fault = new FaultTO(NO_CONNECTIONS);
            }
            if (result.fault != null)
            {
                return result;
            }
            try
            {
                IndexedHashtable t = mySession.ConnectionSet.disconnectRemotes();
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray getVistaTimestamps()
        {
            TaggedTextArray result = new TaggedTextArray();

            if(mySession.ConnectionSet.Count == 0 || !mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO(NO_CONNECTIONS);
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = mySession.ConnectionSet.query("IToolsDao", "getTimestamp", new object[] { });
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedText siteHasPatch(string patchId)
        {
            TaggedText result = new TaggedText();
            if (mySession.ConnectionSet.Count == 0 || !mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO(NO_CONNECTIONS);
            }
            else if (String.IsNullOrEmpty(patchId))
            {
                result.fault = new FaultTO("Missing patchId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                bool f = mySession.ConnectionSet.BaseConnection.hasPatch(patchId);
                result = new TaggedText(mySession.ConnectionSet.BaseConnection.DataSource.SiteId.Id,
                    f == true ? "Y" : "N");
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

        public TaggedTextArray sitesHavePatch(string sitelist, string patchId)
        {
            TaggedTextArray result = new TaggedTextArray();
            //if (mySession.ConnectionSet.Count == 0 || !mySession.ConnectionSet.IsAuthorized)
            //{
            //    result.fault = new FaultTO(NO_CONNECTIONS);
            //}
            if (String.IsNullOrEmpty(sitelist))
            {
                result.fault = new FaultTO("Missing sitelist");
            }
            else if (String.IsNullOrEmpty(patchId))
            {
                result.fault = new FaultTO("Missing patchId");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                AccountLib acctLib = new AccountLib(mySession);
                TaggedTextArray sites = acctLib.visitSites(MdwsConstants.MY_SECURITY_PHRASE, sitelist, MdwsConstants.CPRS_CONTEXT);

                ToolsApi api = new ToolsApi();
                IndexedHashtable t = api.hasPatch(mySession.ConnectionSet, patchId);
                result = new TaggedTextArray(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            finally
            {
                mySession.ConnectionSet.disconnectAll();
            }
            return result;
        }

        public TextArray getRpcs()
        {
            if (mySession == null || mySession.ConnectionSet == null || mySession.ConnectionSet.Count == 0 ||
                !(mySession.ConnectionSet.BaseConnection is VistaConnection) || 
                ((VistaConnection)(mySession.ConnectionSet.BaseConnection)).Rpcs == null ||
                ((VistaConnection)(mySession.ConnectionSet.BaseConnection)).Rpcs.Count == 0)
            {
                TextArray result = new TextArray();
                result.fault = new FaultTO("No active connections");
                return result;
            }

            return new TextArray(((VistaConnection)(mySession.ConnectionSet.BaseConnection)).Rpcs);
        }
    }
}
