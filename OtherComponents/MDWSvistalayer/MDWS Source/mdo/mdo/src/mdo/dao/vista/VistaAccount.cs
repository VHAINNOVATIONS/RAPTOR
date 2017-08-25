using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.sql.UserValidation;
using gov.va.medora.mdo.src.mdo;
using gov.va.medora.mdo.conf;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaAccount : AbstractAccount
    {
        internal static Dictionary<string, string> administrativeDfns = new MdoConfiguration
            (true, ConfigFileConstants.CONFIG_FILE_NAME).AllConfigs[ConfigFileConstants.SERVICE_ACCOUNT_CONFIG_SECTION]; 

        public VistaAccount(AbstractConnection cxn) : base(cxn) { }

        //public override string authenticate(AbstractCredentials credentials)
        //{
        //    return authenticate(credentials, null);
        //}

        public override string authenticate(AbstractCredentials credentials, DataSource validationDataSource = null)
        {
            if (Cxn == null || !Cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Must have connection");
            }
            if (credentials == null)
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing credentials");
            }
            if (string.IsNullOrEmpty(AuthenticationMethod))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing Account AuthenticationMethod");
            }
            if (AuthenticationMethod == VistaConstants.LOGIN_CREDENTIALS)
            {
                return login(credentials);
            }
                // Temporarily disabled - will do only V2WEB for now
            //else if (AuthenticationMethod == VistaConstants.BSE_CREDENTIALS_V2V)
            //{
            //    VisitTemplate visitTemplate = new BseVista2VistaVisit(this, credentials);
            //    return visitTemplate.visit();
            //}
            else if (AuthenticationMethod == VistaConstants.BSE_CREDENTIALS_V2WEB)
            {
                VisitTemplate visitTemplate = new BseVista2WebVisit(this, credentials, validationDataSource);
                return visitTemplate.visit();
            }
            else if (AuthenticationMethod == VistaConstants.NON_BSE_CREDENTIALS)
            {
                VisitTemplate visitTemplate = new NonBseVisit(this, credentials);
                return visitTemplate.visit();
            }
            else
            {
                throw new ArgumentException("Invalid credentials");
            }
        }

        internal string login(AbstractCredentials credentials)
        {
            if (String.IsNullOrEmpty(credentials.AccountName))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing Access Code");
            }
            if (String.IsNullOrEmpty(credentials.AccountPassword))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_NULL, "Missing Verify Code");
            }

            VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
            string rtn = (string)Cxn.query(vq);
            if (rtn == null)
            {
                throw new UnexpectedDataException("Unable to setup authentication");
            }

            vq = new VistaQuery("XUS AV CODE");

            // This is here so we can test with MockConnection
            if (Cxn.GetType().Name != "MockConnection")
            {
                vq.addEncryptedParameter(vq.LITERAL, credentials.AccountName + ';' + credentials.AccountPassword);
            }
            else
            {
                vq.addParameter(vq.LITERAL, credentials.AccountName + ';' + credentials.AccountPassword);
            }
            rtn = (string)Cxn.query(vq);

            //TODO - need to catch renew verify id error

            string[] flds = StringUtils.split(rtn, StringUtils.CRLF);
            if (flds[0] == "0")
            {
                throw new UnauthorizedAccessException(flds[3]);
            }
            AccountId = flds[0];

            // Set the connection's UID
            Cxn.Uid = AccountId;

            // Save the credentials
            credentials.LocalUid = AccountId;
            credentials.AuthenticationSource = Cxn.DataSource;
            credentials.AuthenticationToken = Cxn.DataSource.SiteId.Id + '_' + AccountId;

            IsAuthenticated = true;
            Cxn.IsRemote = false;

            // Set the greeting if there is one
            if (flds.Length > 7)
            {
                return flds[7];
            }
            return "OK";
        }

        public override User authorize(AbstractCredentials credentials, AbstractPermission permission)
        {
            if (permission == null)
            {
                throw new ArgumentNullException("permission");
            }
            checkAuthorizeReadiness();
            checkPermissionString(permission.Name);
            doTheAuthorize(credentials, permission);
            return toUser(credentials);
        }

        internal void doTheAuthorize(AbstractCredentials credentials, AbstractPermission permission)
        {
            //// if we are requesting CPRS context with a visit and user does not have it - add it to their account
            if (permission.Name == VistaConstants.CPRS_CONTEXT &&
                !hasPermission(this.Cxn.Account.Permissions, new MenuOption(VistaConstants.CPRS_CONTEXT)) &&
                !Cxn.Account.AuthenticationMethod.Equals(VistaConstants.LOGIN_CREDENTIALS))
            {
                addContextInVista(Cxn.Uid, permission);
            }

            if (permission != null && !String.IsNullOrEmpty(permission.Name))
            {
                setContext(permission);
            }

            if (String.IsNullOrEmpty(Cxn.Uid))
            {
                if (String.IsNullOrEmpty(credentials.FederatedUid))
                {
                    throw new MdoException("Missing federated UID, cannot get local UID");
                }
                VistaUserDao dao = new VistaUserDao(Cxn);
                Cxn.Uid = dao.getUserIdBySsn(credentials.FederatedUid);
                if (String.IsNullOrEmpty(Cxn.Uid))
                {
                    throw new MdoException("Unable to get local UID for federated ID " + credentials.FederatedUid);
                }
            }
            if (!credentials.Complete)
            {
                VistaUserDao dao = new VistaUserDao(Cxn);
                dao.addVisitorInfo(credentials);
            }
        }

        internal User toUser(AbstractCredentials credentials)
        {
            User u = new User();
            u.Uid = Cxn.Uid;
            u.Name = new PersonName(credentials.SubjectName);
            u.SSN = new SocSecNum(credentials.FederatedUid);
            u.LogonSiteId = Cxn.DataSource.SiteId;
            return u;
        }

        internal void checkAuthorizeReadiness()
        {
            if (Cxn == null || !Cxn.IsConnected)
            {
                throw new ConnectionException("Must have connection");
            }
            if (!isAuthenticated)
            {
                throw new UnauthorizedAccessException("Account must be authenticated");
            }
        }

        internal void checkPermissionString(string permission)
        {
            if (String.IsNullOrEmpty(permission))
            {
                throw new UnauthorizedAccessException("Must have a context");
            }
        }

        internal void setContext(AbstractPermission permission)
        {
            if (permission == null || string.IsNullOrEmpty(permission.Name))
            {
                throw new ArgumentNullException("permission");
            }

            MdoQuery request = buildSetContextRequest(permission.Name);
            string response = "";
            try
            {
                response = (string)Cxn.query(request);
            }
            catch (MdoException e)
            {
                response = e.Message;
            }
            if (response != "1")
            {
                throw getException(response);
            }
            if (!hasPermission(Cxn.Account.Permissions, permission))
            {
                Cxn.Account.Permissions.Add(permission.Name, permission);
            }
            isAuthorized = isAuthorized || permission.IsPrimary;
        }

        internal MdoQuery buildSetContextRequest(string context)
        {
            VistaQuery vq = new VistaQuery("XWB CREATE CONTEXT");
            if (Cxn.GetType().Name != "MockConnection")
            {
                vq.addEncryptedParameter(vq.LITERAL, context);
            }
            else
            {
                vq.addParameter(vq.LITERAL, context);
            }
            return vq;
        }

        internal Exception getException(string result)
        {
            if (result.IndexOf("The context") != -1 &&
                result.IndexOf("does not exist on server") != -1)
            {
                return new PermissionNotFoundException(result);
            }
            if (result.IndexOf("User") != -1 &&
                result.IndexOf("does not have access to option") != -1)
            {
                return new UnauthorizedAccessException(result);
            }
            if (result.StartsWith("Option locked"))
            {
                return new PermissionLockedException(result);
            }
            return new Exception(result);
        }

        //internal void setVisitorContext(AbstractPermission requestedContext, string DUZ)
        //{
        //    try
        //    {
        //        setContext(requestedContext);
        //        return;
        //    }
        //    catch (UnauthorizedAccessException uae)
        //    {
        //        addContextInVista(DUZ, requestedContext);
        //        setContext(requestedContext);
        //    }
        //    catch (Exception e)
        //    {
        //        throw;
        //    }
        //}

        // This is how the visitor gets the requested context - typically 
        // OR CPRS GUI CHART. The visitor comes back from VistA with CAPRI
        // context only.
        internal void addContextInVista(string duz, AbstractPermission requestedContext)
        {
            //if (!Permissions.ContainsKey(VistaConstants.MDWS_CONTEXT) && !Permissions.ContainsKey(VistaConstants.DDR_CONTEXT))
            //{
            //    throw new ArgumentException("User does not have correct menu options to add new context");
            //}
            if (hasPermission(this.Cxn.Account.Permissions, requestedContext))
            {
                return;
            }
            //setContext(Permissions[VistaConstants.DDR_CONTEXT]); // tbd - needed? i think this is superfluous
            VistaUserDao dao = new VistaUserDao(Cxn);

            // try/catch should fix: http://trac.medora.va.gov/web/ticket/2288
            try
            {
                setContext(requestedContext);
            }
            catch (Exception)
            {
                try
                {
                    // will get CONTEXT HAS NOT BEEN CREATED if we don't set this again after failed attempt
                    setContext(new MenuOption(VistaConstants.DDR_CONTEXT));
                    dao.addPermission(duz, requestedContext);
                    setContext(requestedContext);
                }
                catch (Exception)
                {
                    throw;
                }
            }
        }

        internal bool hasPermission(Dictionary<string, AbstractPermission> permissions, AbstractPermission permission)
        {
            bool found = false;

            // turns out this permissions collection has not been implemented consistently. in some places, the permission
            // name is used as the key. in others, most notably the call to get a user's list of options from Vista, the
            // IEN is used as the key. so, this method should check both possible locations
            if (permissions.ContainsKey(permission.Name))
            {
                return true;
            }

            foreach (AbstractPermission perm in permissions.Values)
            {
                if (String.Equals(perm.Name, permission.Name, StringComparison.CurrentCultureIgnoreCase))
                {
                    found = true;
                    break;
                }
            }

            return found;
        }

        //public override string authenticateAndAuthorize(CredentialSet credentials, string permissionString)
        //{
        //    string duz = authenticate();
        //    authorize("");
        //    return duz;
        //}

        public override User authenticateAndAuthorize(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource = null)
        {
            string msg = authenticate(credentials, validationDataSource);
            User u = authorize(credentials, permission);
            u.Greeting = msg;
            return u;
        }

        //public void addDdrContext(AbstractCredentials credentials, AbstractConnection currentCxn)
        //{
        //    if (credentials.AuthenticationMethod == VistaConstants.LOGIN_CREDENTIALS)
        //    {
        //        throw new UnauthorizedAccessException("Wrong credential type");
        //    }
        //    VistaConnection cxn = new VistaConnection(currentCxn.DataSource);
        //    cxn.connect();
        //    cxn.Account.authenticate(credentials);
        //    cxn.disconnect();
        //}

        public string getAuthenticationTokenFromVista()
        {
            VistaQuery vq = new VistaQuery("XUS SET VISITOR");
            return (string)Cxn.query(vq);
        }

        public string makeAuthenticationTokenInVista(string duz)
        {
            string token1 = getAuthenticationTokenFromVista2();
            string token2 = setAuthenticationTokenInVista(token1, duz);
            token2 = getXtmpToken(token1);
            return token1;
        }

        internal string getXtmpToken(string token)
        {
            string arg = "$G(^XTMP(\"" + token + "\",1))";
            return VistaUtils.getVariableValue(Cxn, arg);
        }

        internal string setAuthenticationTokenInVista(string token, string duz)
        {
            DdrLister query = buildSetAuthenticationTokenInVistaQuery(token, duz);
            string[] response = query.execute();
            if (response.Length != 1)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }
            return response[0];
        }

        internal string getAuthenticationTokenFromVista2()
        {
            DdrLister query = buildGetAuthenticationTokenFromVista2Query();
            string[] response = query.execute();
            if (response == null || response.Length != 1)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }
            string[] flds = response[0].Split(new char[] { '^' });
            if (flds.Length != 3)
            {
                throw new MdoException(MdoExceptionCode.DATA_UNEXPECTED_FORMAT);
            }
            return flds[2];
        }

        internal DdrLister buildGetAuthenticationTokenFromVista2Query()
        {
            DdrLister query = new DdrLister(Cxn);
            query.File = "200";
            query.Fields = ".01";
            query.Flags = "IP";
            query.Max = "1";
            query.Xref = "#";
            query.Id = "D EN^DDIOL($$HANDLE^XUSRB4(\"XUSBSE\",1))";
            return query;
        }

        internal DdrLister buildSetAuthenticationTokenInVistaQuery(string token, string duz)
        {
            DdrLister query = new DdrLister(Cxn);
            query.File = "200";
            query.Fields = ".01";
            query.Flags = "IP";
            query.Max = "1";
            query.Xref = "#";
            query.Id = "S ^XTMP(\"" + token + "\",1)=$$GET^XUESSO1(" + duz + ")";
                        //"D EN^DDIOL(\"^XMTP(\"" + token + "\",1)";
            return query;
        }

        public static string getAdminLocalUid(string siteId)
        {

            if (administrativeDfns.ContainsKey(siteId))
            {
                return administrativeDfns[siteId];
            }
            else
            {
                return "1";
            }
        }
    }

    abstract class VisitTemplate
    {
        protected VistaAccount acct;
        protected AbstractConnection cxn;
        protected AbstractCredentials creds;

        public VisitTemplate(VistaAccount acct, AbstractCredentials credentials)
        {
            this.acct = acct;
            cxn = acct.Cxn;
            creds = credentials;
        }

        public abstract void setupVisit();
        public abstract MdoQuery buildVisitRequest();
        public abstract bool success(string[] flds);

        public string visit()
        {
            if (creds.FederatedUid == VistaConstants.ADMINISTRATIVE_FEDERATED_UID)
            {
                creds.LocalUid = VistaAccount.getAdminLocalUid(creds.AuthenticationSource.SiteId.Id);
            }
            validateCredentials();
            setupVisit();
            MdoQuery request = buildVisitRequest();
            string response = (string)cxn.query(request);
            string[] flds = StringUtils.split(response, StringUtils.CRLF);
            if (!success(flds))
            {
                throw new UnauthorizedAccessException("Visit failed: Invalid credentials?");
            }
            if (flds.Length >= 8)
            {
                cxn.IsTestSource = (flds[7] == "0");
            }

            acct.IsAuthenticated = true;
            cxn.IsRemote = true;

            //creds.AuthenticatorId = cxn.DataSource.SiteId.Id;
            //creds.AuthenticatorName = cxn.DataSource.SiteId.Name;

            return "OK";
        }

        internal void validateCredentials()
        {
            // Need an app password (the security phrase)...
            if (String.IsNullOrEmpty(creds.SecurityPhrase))
            {
                throw new UnauthorizedAccessException("Missing application password");
            }

            // BSE visit uses just the authentication token, so...
            if (String.IsNullOrEmpty(creds.AuthenticationToken))
            {
                throw new UnauthorizedAccessException("Cannot visit without authentication token");
            }

            //string[] flds = creds.AuthenticationToken.Split(new char[] { '_' });
            //if (flds.Length != 2)
            //{
            //    throw new UnauthorizedAccessException("Invalid authentication token");
            //}

            if (String.IsNullOrEmpty(creds.SubjectPhone))
            {
                creds.SubjectPhone = "No phone";
            }

            if (String.IsNullOrEmpty(creds.FederatedUid))
            {
                throw new UnauthorizedAccessException("Missing FederatedUid");
            }

            if (String.IsNullOrEmpty(creds.SubjectName))
            {
                throw new UnauthorizedAccessException("Mising SubjectName");
            }

            if (creds.AuthenticationSource == null)
            {
                throw new UnauthorizedAccessException("Missing AuthenticationSource");
            }

            if (creds.AuthenticationSource.SiteId == null)
            {
                throw new UnauthorizedAccessException("Missing AuthenticationSource.SiteId");
            }

            if (String.IsNullOrEmpty(creds.AuthenticationSource.SiteId.Id))
            {
                throw new UnauthorizedAccessException("Missing AuthenticatorId");
            }

            if (String.IsNullOrEmpty(creds.AuthenticationSource.SiteId.Name))
            {
                throw new UnauthorizedAccessException("Missing AuthenticatorName");
            }

            if (String.IsNullOrEmpty(creds.LocalUid))
            {
                throw new UnauthorizedAccessException("Missing LocalUid");
            }

            if (String.IsNullOrEmpty(creds.SubjectPhone))
            {
                throw new UnauthorizedAccessException("Missing SubjectPhone");
            }

            // Is the connection to a production system with a test visitor from a different site?
            if (!cxn.IsTestSource && creds.AreTest && creds.AuthenticationSource.SiteId.Id != cxn.DataSource.SiteId.Id)
            {
                throw new UnauthorizedAccessException("Cannot visit production system with test credentials");
            }
        }
    }

    // Temporarily disabled - only V2WEB BSE visits for now.
    //class BseVista2VistaVisit : VisitTemplate
    //{
    //    public BseVista2VistaVisit(VistaAccount acct, AbstractCredentials creds) : base(acct, creds) { }

    //    public override void setupVisit() { }

    //    public override MdoQuery buildVisitRequest()
    //    {
    //        VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
    //        string phrase = creds.SecurityPhrase + '^' +
    //                        creds.AuthenticationToken + '^' +
    //                        creds.AuthenticationSource.SiteId.Id + '^' +
    //                        creds.AuthenticationSource.Port;
    //        string arg = "-35^" + VistaUtils.encrypt(phrase);
    //        vq.addParameter(vq.LITERAL, arg);
    //        return vq;
    //    }

    //    public override bool success(string[] flds)
    //    {
    //        if (String.IsNullOrEmpty(creds.AuthenticationMethod))
    //        {
    //            creds.AuthenticationMethod = VistaConstants.BSE_CREDENTIALS_V2V;
    //        }

    //        // NB: Do NOT remove this code.  It is the only way to detect that a BSE visit has
    //        // failed.  If this is bypassed, the subsequent context call seems to succeed when
    //        // it doesn't.
    //        if (flds.Length < 6 || flds[5] != "1")
    //        {
    //            return false;
    //        }
    //        return true;
    //    }
    //}

    class BseVista2WebVisit : VisitTemplate
    {
        DataSource _validatorDataSource;

        public BseVista2WebVisit(VistaAccount acct, AbstractCredentials creds, DataSource validatorDataSource) 
            : base(acct, creds) 
        {
            if (validatorDataSource != null)
            {
                _validatorDataSource = validatorDataSource;
            }
        }

        public override void setupVisit()
        {
            insertUserValidationRecord();
        }

        public override MdoQuery buildVisitRequest()
        {
            VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
            string token = SSTCryptographer.Encrypt(creds.AuthenticationToken, VistaConstants.ENCRYPTION_KEY);
            token = UserValidationDao.escapeString(token);
            string arg = "-35^" + VistaUtils.encrypt(creds.SecurityPhrase + '^' + token);
            vq.addParameter(vq.LITERAL, arg);
            return vq;
        }

        public override bool success(string[] flds)
        {
            // NB: Do NOT remove this code.  It is the only way to detect that a BSE visit has
            // failed.  If this is bypassed, the subsequent context call seems to succeed when
            // it doesn't.
            if (flds.Length < 6 || flds[5] != "1")
            {
                deleteUserValidationRecord();
                return false;
            }

            return true;
        }

        internal void insertUserValidationRecord()
        {
            UserValidationDao dao = new UserValidationDao(new UserValidationConnection(_validatorDataSource));
            dao.addRecord(creds, VistaConstants.ENCRYPTION_KEY);
        }

        internal void deleteUserValidationRecord()
        {
            UserValidationDao dao = new UserValidationDao(new UserValidationConnection(_validatorDataSource));
            dao.deleteRecord(creds.AuthenticationToken, VistaConstants.ENCRYPTION_KEY);
        }
    }

    class NonBseVisit : VisitTemplate
    {
        public NonBseVisit(VistaAccount acct, AbstractCredentials creds) : base(acct, creds) { }

        public override void setupVisit() { }

        public override MdoQuery buildVisitRequest()
        {
            string arg = "-31^DVBA_^";
            arg += creds.FederatedUid + '^' +
                creds.SubjectName + '^' +
                creds.AuthenticationSource.SiteId.Name + '^' +
                creds.AuthenticationSource.SiteId.Id + '^' +
                creds.LocalUid + '^' +
                creds.SubjectPhone;
            VistaQuery vq = new VistaQuery("XUS SIGNON SETUP");
            vq.addParameter(vq.LITERAL, arg);
            return vq;
        }

        public override bool success(string[] flds)
        {
            // Set DDR context in order to add the requested context
            AbstractPermission ddrContext = new MenuOption(VistaConstants.DDR_CONTEXT);
            acct.setContext(ddrContext);

            // Get the UID while we have DDR context set anyway
            VistaUserDao dao = new VistaUserDao(cxn);
            cxn.Uid = dao.getUserIdBySsn(creds.FederatedUid);

            // Add the requested context to the user's account
            //acct.addContextInVista(cxn.Uid, acct.PrimaryPermission);

            return true;
        }
    }
}
