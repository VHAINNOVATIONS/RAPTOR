using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws.rest
{
    public class ConnectionLib
    {
        MySession _mySession;

        public ConnectionLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public DataSourceArray connectToLoginSite(string sitecode)
        {
            // TODO - FIX!!! This is very ugly - here so that SOAP and REST services can both be stateful or stateless
            if (!Convert.ToBoolean(_mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            {
                return new gov.va.medora.mdws.ConnectionLib(_mySession).connectToLoginSite(sitecode);
            }

            DataSourceArray result = new DataSourceArray();

            try
            {
                MdwsUtils.checkNullArgs(MdwsUtils.getArgsDictionary(System.Reflection.MethodInfo.GetCurrentMethod().GetParameters(), new List<object>() { sitecode }));
                MdwsUtils.checkSiteTable(_mySession, sitecode);
                MdwsUtils.checkConnections(_mySession, sitecode);

                Site site = _mySession.SiteTable.getSite(sitecode);
                DataSource src = site.getDataSourceByModality("HIS");
                AbstractDaoFactory factory = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(src.Protocol));

                // REST
                SessionMgr.getInstance().setConnection(_mySession, sitecode);
                // END REST
                result = new DataSourceArray(src);

                result.items[0].welcomeMessage = "TODO - implement cached connection messages in pool"; 
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            finally
            {
                //RestSessionMgr.getInstance().returnConnections(_mySession);
            }
            return result;
        }

        public TaggedTextArray disconnectAll()
        {
            // TODO - FIX!!! This is very ugly - here so that SOAP and REST services can both be stateful or stateless
            if (!Convert.ToBoolean(_mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            {
                return new gov.va.medora.mdws.ConnectionLib(_mySession).disconnectAll();
            }

            TaggedTextArray result = new TaggedTextArray();
            try
            {
                //IndexedHashtable t = _mySession.ConnectionSet.disconnectAll();
                //result = new TaggedTextArray(t);
                _mySession.ConnectionSet.Clear();
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }

            return result;
        }


    }
}