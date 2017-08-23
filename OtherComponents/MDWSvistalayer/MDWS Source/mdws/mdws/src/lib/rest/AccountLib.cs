using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using gov.va.medora.mdws.dto;
using System.Reflection;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.dao.vista;
using gov.va.medora.mdo.domain.pool.connection;
using gov.va.medora.mdo.api;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws.rest
{
    public class AccountLib
    {
        MySession _mySession;

        public AccountLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public UserTO login(string accountUsername, string accountPwd, string permissionString = null)
        {
            // TODO - FIX!!! This is very ugly - here so that SOAP and REST services can both be stateful or stateless
            if (!Convert.ToBoolean(_mySession.MdwsConfiguration.AllConfigs[MdwsConfigConstants.CONNECTION_POOL_CONFIG_SECTION][MdwsConfigConstants.CONNECTION_POOLING]))
            {
                return new gov.va.medora.mdws.AccountLib(_mySession).login(accountUsername, accountPwd, permissionString);
            }

            UserTO result = new UserTO();
            AbstractConnection c = null;
            try
            {
                MdwsUtils.checkNullArgs(MdwsUtils.getArgsDictionary(
                    System.Reflection.MethodInfo.GetCurrentMethod().GetParameters(), new List<object>() 
                    { _mySession.ConnectionSet.BaseSiteId, accountUsername, accountPwd, permissionString }));

                c = _mySession.ConnectionSet.BaseConnection;

                AbstractCredentials credentials = AbstractCredentials.getCredentialsForCxn(c);
                credentials.AccountName = accountUsername;
                credentials.AccountPassword = accountPwd;
                c.Account.AuthenticationMethod = MdwsConstants.LOGIN_CREDENTIALS;
                if (String.IsNullOrEmpty(permissionString))
                {
                    permissionString = MdwsConstants.CPRS_CONTEXT;
                }
                _mySession.PrimaryPermission = new MenuOption(permissionString);
                _mySession.User = c.Account.authenticateAndAuthorize(credentials, _mySession.PrimaryPermission);
                _mySession.Credentials = credentials;

                // REST
                _mySession.setAuthorized(_mySession.ConnectionSet.BaseSiteId); // TODO - revisit, need to mark connections as authorized but more-so need to cache the symbol table
                // END REST
                
                result = new UserTO(_mySession.User);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            finally
            {
                // REST
                //RestSessionMgr.getInstance().returnConnections(_mySession);
                // END REST
            }

            return result;
        }

    }
}