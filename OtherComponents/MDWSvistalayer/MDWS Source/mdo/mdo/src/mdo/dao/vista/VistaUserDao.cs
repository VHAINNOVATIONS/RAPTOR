using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Collections;
using System.Threading;
using System.Text;
using gov.va.medora.mdo;
using gov.va.medora.utils;
using gov.va.medora.mdo.exceptions;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaUserDao : IUserDao
    {
        AbstractConnection cxn = null;

        public VistaUserDao(AbstractConnection cxn)
        {
            this.cxn = cxn;
        }

        //=========================================================================================
        // User Lookups
        //=========================================================================================

        /// <summary>
        /// Lookup a user. Supported lookups:
        /// 
        /// Name
        /// SSN
        /// DUZ
        /// 
        /// Specify the lookup type by the param.Key. For example: userLookup(new KeyValuePair<string, string>("NAME", "SMITH,JOHN"))
        /// </summary>
        /// <param name="param"></param>
        /// <returns></returns>
        public User[] userLookup(KeyValuePair<string, string> param)
        {
            string maxrex = null;
            if (param.Key == "NAME")
            {
                maxrex = "50";
            }
            return userLookup(param, maxrex);
        }

        public User[] userLookup(KeyValuePair<string, string> param, string maxrex)
        {
            string ky = param.Key.ToUpper();
            string vl = param.Value.ToUpper();
            if (ky == "ID")
            {
                User u = userLookupByDuz(vl);
                if (u == null)
                {
                    return null;
                }
                return new User[] { u };
            }
            if (ky == "SSN")
            {
                User u = userLookupBySsn(vl);
                if (u == null)
                {
                    return null;
                }
                return new User[] { u };
            }
            if (ky == "NAME")
            {
                // TBD: what to do about this max rex
                return userLookupByName(vl, maxrex);
            }
            throw new ArgumentException("lookup by " + param.Key + " not currently implemented");
        }

        internal User userLookupByDuz(string duz)
        {
            DdrLister query = buildUserLookupQueryByDuz(duz);
            string[] response = query.execute();
            return toUser(response);
        }

        internal User userLookupBySsn(string ssn)
        {
            DdrLister query = buildUserLookupQueryBySsn(ssn);
            string[] response = query.execute();
            return toUser(response);
        }

        internal User[] userLookupByName(string target, string maxRex)
        {
            DdrLister query = buildUserLookupByNameQuery(target, maxRex);
            string[] response = query.execute();
            return toUsers(response);
        }

        public User[] providerLookup(KeyValuePair<string, string> param)
        {
            if (param.Key == "NAME")
            {
                return providerLookupByName(param.Value);
            }
            throw new ArgumentException("Only name lookup currently implemented");
        }

        internal User[] providerLookupByName(string target)
        {
            MdoQuery request = buildProviderLookup(target);
            string response = (string)cxn.query(request);
            return toUsers(response);
        }

        public bool isUser(string duz)
        {
            if (!VistaUtils.isWellFormedDuz(duz))
            {
                throw new InvalidlyFormedRecordIdException(duz);
            }
            string arg = "$D(^VA(200," + duz + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            return (response == "1");
        }

        public User getUser(string duz)
        {
            DdrLister query = buildGetUserQuery(duz);
            string[] response = query.execute();
            User result = toUserFromGetUser(response);
            //result.MenuOptions = getMenuOptions(duz);
            ////result.DelegatedOptions = getDelegatedOptions(duz);
            //result.SecurityKeys = getSecurityKeys(duz);
            //result.Divisions = getDivisions(duz);
            return result;
        }

        internal DdrLister buildGetUserQuery(string duz)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200";
            query.Fields = "@;.01;.132;.137;.138;.141;8;9;29";
            query.Flags = "IP";
            query.Max = "1";
            query.From = VistaUtils.adjustForNumericSearch(duz);
            query.Part = duz;
            query.Xref = "#";
            return query;
        }

        internal User toUserFromGetUser(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            string[] rex = StringUtils.split(response[0], StringUtils.CRLF);
            rex = StringUtils.trimArray(rex);
            if (rex.Length != 1)
            {
                throw new Exception("Multiple records returned");
            }
            string[] flds = StringUtils.split(rex[0], StringUtils.CARET);
            User result = new User();
            result.Uid = flds[0];
            result.Name = new PersonName(flds[1]);
            result.Phone = flds[2];
            result.VoicePager = flds[3];
            if (flds[4] != "")
            {
                result.VoicePager += (flds[3] != "" ? "/" : "") + flds[4];
            }
            result.Office = flds[5];
            if (flds[6] != "")
            {
                result.Title = getUserTitle(flds[6]);
            }
            result.SSN = new SocSecNum(flds[7]);
            if (flds[8] != "")
            {
                result.Service = getUserService(flds[8]);
            }
            return result;
        }

        //-----------------------------------------------------------------------------------------

        internal DdrLister buildUserLookupQueryByDuz(string duz)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200";

            // E flag note
            query.Fields = "@;.01;.132;.137;.141;3;4;5;7;8E;9;13;20.2;20.3;29;29E;53.5;53.5E;53.6;201;201E";

            query.Flags = "IP";
            query.Max = "1";
            query.From = VistaUtils.adjustForNumericSearch(duz);
            query.Part = duz;
            query.Xref = "#";
            return query;
        }

        internal DdrLister buildUserLookupQueryBySsn(string ssn)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200";

            // E flag note
            query.Fields = "@;.01;.132;.137;.141;3;4;5;7;8E;9;13;20.2;20.3;29;29E;53.5;53.5E;53.6;201;201E";

            query.Flags = "IP";
            query.Max = "1";
            query.From = VistaUtils.adjustForNumericSearch(ssn);
            query.Part = ssn;
            query.Xref = "SSN";
            return query;
        }

        internal User toUser(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            string[] flds = StringUtils.split(response[0], StringUtils.CARET);
            User result = new User();
            result.Uid = flds[0];
            result.Name = new PersonName(flds[1]);
            result.Phone = flds[2];
            result.Office = flds[4];
            //flds[5] is FM access
            result.Gender = flds[6];
            result.DOB = VistaTimestamp.toUtcString(flds[7]);
            //flds[8] is disuser
            result.Title = flds[9];
            result.SSN = new SocSecNum(flds[10]);
            //flds[11] is nickname
            result.SigText = flds[12] + "\r\n" + flds[13];
            if (flds[14] != "" || flds[15] != "")
            {
                result.Service = new Service();
                result.Service.Id = flds[14];
                result.Service.Name = flds[15];
            }
            //flds[16] is Provider Class IEN
            result.UserClass = flds[17];
            result.PrimaryPermission = new MenuOption(flds[19], flds[20]);
            return result;
        }

        internal MdoQuery buildProviderLookup(string target)
        {
            target = VistaUtils.adjustForNameSearch(target);
            VistaQuery vq = new VistaQuery("ORWU NEWPERS");
            vq.addParameter(vq.LITERAL, target.ToUpper());
            vq.addParameter(vq.LITERAL, "1");
            //vq.addParameter(vq.LITERAL, "PROVIDER"); // see ticket http://trac.medora.va.gov/web/ticket/3091
            return vq;
        }

        internal User[] toUsers(String rtn)
        {
            ArrayList lst = new ArrayList();
            String[] entries = StringUtils.split(rtn, StringUtils.CRLF);
            for (int i = 0; i < entries.Length; i++)
            {
                if (entries[i] == "")
                {
                    continue;
                }
                String[] flds = StringUtils.split(entries[i], StringUtils.CARET);
                if (StringUtils.isNumeric(flds[0]))
                {
                    User user = new User();
                    user.Uid = flds[0];
                    user.setName(flds[1]);
                    if (flds.Length > 2 && !String.IsNullOrEmpty(flds[2]))
                    {
                        user.Title = flds[2];
                    }
                    lst.Add(user);
                }
            }
            return (User[])lst.ToArray(typeof(User));
        }

        internal DdrLister buildUserLookupByNameQuery(string target, string maxRex)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200";

            // E flag note
            query.Fields = "@;.01;.132;.137;.141;3;4;5;7;8E;9;13;20.2;20.3;29;29E;53.5;53.5E;53.6;201;201E";

            query.Flags = "IP";
            query.Max = maxRex;
            target = target.ToUpper();
            query.From = VistaUtils.adjustForNameSearch(target);
            query.Part = target;
            query.Xref = "B";
            return query;
        }

        internal User[] toUsers(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            response = StringUtils.trimArray(response);
            User[] result = new User[response.Length];
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                result[i] = new User();
                result[i].Uid = flds[0];
                result[i].Name = new PersonName(flds[1]);
                result[i].Phone = flds[2];
                result[i].Office = flds[4];
                //flds[5] is FM access
                result[i].Gender = flds[6];
                result[i].DOB = VistaTimestamp.toUtcString(flds[7]);
                //flds[8] is disuser
                result[i].Title = flds[9];
                result[i].SSN = new SocSecNum(flds[10]);
                //flds[11] is nickname
                result[i].SigText = flds[12] + "\r\n" + flds[13];
                //flds[14] is service IEN
                if (flds[15] != "")
                {
                    result[i].Service = new Service();
                    result[i].Service.Name = flds[15];
                }
                //flds[16] is Provider Class IEN
                result[i].UserClass = flds[17];
                result[i].PrimaryPermission = new MenuOption(flds[19], flds[20]);
            }
            return result;
        }

        //=========================================================================================
        // User Properties
        //=========================================================================================

        public string getUserId(KeyValuePair<string, string> param)
        {
            if (param.Key == "SSN")
            {
                return getUserIdBySsn(param.Value);
            }
            throw new ArgumentException("Only SSN lookups are currently implemented");
        }

        internal string getUserIdBySsn(string ssn)
        {
            //if (StringUtils.isEmpty(ssn) || !VistaSocSecNum.isValid(ssn))
            //{
            //    throw new ArgumentException("Invalid SSN");
            //}
            string arg = "$O(^VA(200,\"SSN\",\"" + ssn + "\",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            return VistaUtils.errMsgOrIen(response);
        }

        public string getUserTitle(string ien)
        {
            if (StringUtils.isEmpty(ien))
            {
                throw new ArgumentException("Invalid IEN");
            }
            string arg = "$P($G(^DIC(3.1," + ien + ",0)),U,1)";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        public string getProviderClass(string ien)
        {
            if (StringUtils.isEmpty(ien))
            {
                throw new ArgumentException("Invalid IEN");
            }
            string arg = "$P($G(^DIC(7," + ien + ",0)),U,1)";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        public Service getUserService(string ien)
        {
            if (StringUtils.isEmpty(ien))
            {
                throw new ArgumentException("Invalid IEN");
            }
            string arg = "$G(^DIC(49," + ien + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            if (response == "")
            {
                return null;
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            Service result = new Service();
            result.Id = ien;
            if (flds.Length > 0)
            {
                result.Name = flds[0];
            }
            if (flds.Length > 1)
            {
                result.Abbreviation = flds[1];
            }

            if (flds.Length > 2)
            {
                if (flds[2] != "")
                {
                    result.Chief = new User();
                    result.Chief.Uid = flds[2];
                }
            }

            if (flds.Length > 3)
            {
                if (flds[3] != "" && flds[3] != ien)
                {
                    result.ParentService = new Service();
                    result.ParentService.Id = flds[3];
                }
            }

            // Don't care about flds 4 and 5

            if (flds.Length > 6)
            {
                result.Location = flds[6];
            }

            if (flds.Length > 7)
            {
                result.MailSymbol = flds[7];
            }

            if (flds.Length > 8)
            {
                if (flds[8] == "C")
                {
                    result.Type = "PATIENT CARE";
                }
                else if (flds[8] == "A")
                {
                    result.Type = "ADMINISTRATIVE";
                }
                else
                {
                    result.Type = "";
                }
            }
            return result;
        }

        public void setLoginSiteProperties(User user)
        {
            if (user == null || StringUtils.isEmpty(user.Uid))
            {
                throw new ArgumentException("No user ID for site");
            }
            VistaQuery vq = new VistaQuery("ORWU USERINFO");
            string rtn = (String)cxn.query(vq);
            if (rtn == null)
            {
                throw new DataException("Unable to get user info");
            }
            string[] parts = StringUtils.split(rtn, StringUtils.CARET);
            user.Uid = parts[0];
            user.setName(parts[1]);
            user.OrderRole = parts[5];
            vq = new VistaQuery("XWB GET VARIABLE VALUE");
            string arg = "@\"^VA(200," + user.Uid + ",1)\"";
            vq.addParameter(vq.REFERENCE, arg);
            rtn = (string)cxn.query(vq);
            if (rtn == "")
            {
                throw new DataException("Unable to get user SSN");
            }
            parts = StringUtils.split(rtn, StringUtils.CARET);
            user.SSN = new SocSecNum(parts[8]);
        }

        public void setUserInfoAtLogin(User user)
        {
            VistaQuery vq = new VistaQuery("XUS GET USER INFO");
            string response = (string)cxn.query(vq);
            toUserFromLogin(response, user);
        }

        public User getUserInfo(string duz)
        {
            User result = new User();
            result.Uid = duz;
            result.LogonSiteId = cxn.DataSource.SiteId;
            setUserInfo(result);
            return result;
        }

        public void setUserInfo(User user)
        {
            string DUZ = user.Uid;
            string arg = "$P($G(^VA(200," + DUZ + ",0)),\"^\",1)";
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",1)),\"^\",9)";
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",.14)),\"^\",1)";
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",.13)),\"^\",2)";
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",.13)),\"^\",8)";
            arg += "_\"^\"_" + "$P($G(^ECC(730,$S($D(^VA(200," + DUZ + ",\"QAR\")):^VA(200," + DUZ + ",\"QAR\"),1:-1),0)),\"^\",1)";
            arg += "_\"^\"_" + "$G(^DIC(3.1,$S($P(^VA(200," + DUZ + ",0),\"^\",9):$P(^VA(200," + DUZ + ",0),\"^\",9),1:-1),0))";
            string response = "";
            try
            {
                response = VistaUtils.getVariableValue(cxn, arg);
            }
            catch (Exception e)
            {
                if (e.Message.Contains("M  ERROR"))
                {
                    throw new ArgumentException("No such DUZ");
                }
                else
                {
                    throw e;
                }
            }
            setUserProperties(user, response);
        }

        public bool isVisitorAccount(string duz)
        {
            string arg = "$G(^VA(200," + duz + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            if (flds.Length == 1 || flds[2] == "")
            {
                return true;
            }
            return false;
        }

        public void addVisitorInfo(AbstractCredentials credentials)
        {
            string DUZ = credentials.LocalUid;

            string arg = "$P($G(^VA(200," + DUZ + ",0)),\"^\",1)";          //NAME
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",1)),\"^\",9)";
            arg += "_\"^\"_" + "$P($G(^VA(200," + DUZ + ",.13)),\"^\",2)";  //OFFICE PHONE
            string response = "";
            try
            {
                response = VistaUtils.getVariableValue(cxn, arg);
            }
            catch (Exception e)
            {
                if (e.Message.Contains("M  ERROR"))
                {
                    throw new ArgumentException("No such DUZ");
                }
                else
                {
                    throw e;
                }
            }
            if (response == "")
            {
                return;
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            credentials.FederatedUid = flds[1];
            credentials.SubjectName = flds[0];
            credentials.SubjectPhone = flds[2];
            credentials.AuthenticationSource.SiteId.Id = cxn.DataSource.SiteId.Id;
            credentials.AuthenticationSource.SiteId.Name = cxn.DataSource.SiteId.Name;
        }

        //-----------------------------------------------------------------------------------------

        //internal string decodeProviderType(string value)
        //{
        //    if (value == "1")
        //    {
        //        return "FULL TIME";
        //    }
        //    if (value == "2")
        //    {
        //        return "PART TIME";
        //    }
        //    if (value == "3")
        //    {
        //        return "C & A";
        //    }
        //    if (value == "4")
        //    {
        //        return "FEE BASIS";
        //    }
        //    if (value == "5")
        //    {
        //        return "HOUSE STAFF";
        //    }
        //    throw new ArgumentException("Invalid value");
        //}

        internal void toUserFromLogin(string response, User user)
        {
            if (response == "")
            {
                return;
            }
            string[] flds = StringUtils.split(response, StringUtils.CRLF);
            user.Uid = flds[0];
            user.Name = new PersonName(flds[1]);
            string[] subflds = StringUtils.split(flds[3], StringUtils.CARET);
            user.LogonSiteId = new SiteId(subflds[2], subflds[1]);
            user.Title = flds[4];
            if (flds[5] != "")
            {
                user.Service = new Service();
                user.Service.Name = flds[5];
            }
        }

        internal void setUserProperties(User user, string response)
        {
            if (response == "")
            {
                return;
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            user.Name = new PersonName(flds[0]);
            user.SSN = new SocSecNum(flds[1]);
            user.Office = flds[2];
            user.Phone = flds[3];
            user.VoicePager = flds[4];
            if (flds[5] != "")
            {
                user.Service = new Service();
                user.Service.Name = flds[5];
            }
            user.Title = flds[6];
        }

        internal bool hasVisitorAlias(string duz)
        {
            DdrLister query = buildHasVisitorAliasQuery(duz);
            string[] response = query.execute();
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                if (flds[1] == "VISITOR")
                {
                    return true;
                }
            }
            return false;
        }

        internal DdrLister buildHasVisitorAliasQuery(string duz)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200.04";
            query.Iens = "," + duz + ",";
            query.Fields = ".01";
            query.Flags = "IP";
            query.Xref = "#";
            return query;
        }

        // This is testing for presence of A/V codes.  It appears that the VERIFY CODE does not
        // get populated in Vista, but that the ACCESS CODE field holds both codes.  Therefore,
        // we test only 1 field.
        internal bool isTrueCapriUser(Dictionary<string, SecurityKey> keys)
        {
            if (keys == null)
            {
                return false;
            }
            foreach (string k in keys.Keys)
            {
                if (keys[k].Name.StartsWith("DVBA"))
                {
                    return true;
                }
            }
            return false;
        }

        //=========================================================================================
        // Permissions
        //=========================================================================================

        public void setContext(string context)
        {
            MdoQuery request = buildSetContextRequest(context);
            string response = (string)cxn.query(request);
            if (response != "1")
            {
                throw getException(response);
            }
        }

        public void validateLocalAccount(string duz)
        {
            string arg = "$G(^VA(200," + duz + ",0))";
            string response = VistaUtils.getVariableValue(cxn, arg);
            if (response == "")
            {
                throw new UnauthorizedAccessException("No such user");
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            if (flds.Length < 2 || flds[2] == "")
            {
                throw new UnauthorizedAccessException("User has no access code at this site");
            }
            if (flds.Length > 6 && flds[6] != "")
            {
                throw new UnauthorizedAccessException("Account has been disusered");
            }
            if (flds.Length > 10 && flds[10] != "")
            {
                throw new UnauthorizedAccessException("Account has been deactivated");
            }
        }

        public bool isValidEsig(string esig)
        {
            MdoQuery request = buildIsValidEsigRequest(esig);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        public string setNewPassword(string oldVerifyCode, string newVerifyCode)
        {
            VistaQuery vq = new VistaQuery("XUS CVC");
            string oldEncodedVerifyCode = VistaQuery.Parameter.encrypt(oldVerifyCode.ToUpper());
            string newEncodedVerifyCode = VistaQuery.Parameter.encrypt(newVerifyCode.ToUpper());
            vq.addParameter(vq.LITERAL, oldEncodedVerifyCode + '^' + newEncodedVerifyCode + '^' + newEncodedVerifyCode);
            string rtn = (string)cxn.query(vq);
            if (rtn.StartsWith("0"))
            {
                return "OK";
            }
            else
            {
                return rtn.Substring(1);
            }
        }

        //-----------------------------------------------------------------------------------------

        internal MdoQuery buildSetContextRequest(string context)
        {
            VistaQuery vq = new VistaQuery("XWB CREATE CONTEXT");
            vq.addEncryptedParameter(vq.LITERAL, context);
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
            if (result.IndexOf("Option locked") != -1 &&
                result.IndexOf("does not have access to option") != -1)
            {
                return new PermissionLockedException(result);
            }
            return new Exception(result);
        }

        //DP 5/23/2011  Added guard clause to  this query builder methos so it will not save a (one time valid) 
        //encrypted string in the mock connection file.
        internal MdoQuery buildIsValidEsigRequest(string esig)
        {
            VistaQuery vq = new VistaQuery("ORWU VALIDSIG");

            if (cxn.GetType().Name != "MockConnection")
            {
                vq.addEncryptedParameter(vq.LITERAL, esig);
            }
            else
            {
                vq.addParameter(vq.LITERAL, esig);
            }
            //vq.addParameter(vq.LITERAL, esig);

            return vq;
        }

        //=========================================================================================
        // Permissions
        //=========================================================================================

        public AbstractPermission addPermission(string duz, AbstractPermission p)
        {
            if (p.Type == PermissionType.MenuOption)
            {
                p.RecordId = addMenuOption(duz, p);
                return p;
            }
            if (p.Type == PermissionType.DelegatedOption)
            {
                p.RecordId = addDelegatedOption(duz, p);
                return p;
            }
            if (p.Type == PermissionType.SecurityKey)
            {
                return addSecurityKey(duz, p);
            }
            throw new ArgumentException("Invalide permission type");
        }

        public void removePermission(string duz, AbstractPermission p)
        {
            if (p.Type == PermissionType.MenuOption)
            {
                removeMenuOption(duz, p);
            }
            if (p.Type == PermissionType.DelegatedOption)
            {
                removeDelegatedOption(duz, p);
            }
            if (p.Type == PermissionType.SecurityKey)
            {
                removeSecurityKey(duz, p);
            }
            throw new ArgumentException("Invalid permission type");
        }

        public bool hasPermission(string duz, AbstractPermission p)
        {
            if (p.Type == PermissionType.MenuOption)
            {
                return hasMenuOption(duz, p);
            }
            if (p.Type == PermissionType.DelegatedOption)
            {
                return hasDelegatedOption(duz, p);
            }
            if (p.Type == PermissionType.SecurityKey)
            {
                return hasSecurityKey(duz, p);
            }
            throw new ArgumentException("Invalid permission type");
        }

        public Dictionary<string, AbstractPermission> getPermissions(PermissionType type, string duz)
        {
            if (StringUtils.isEmpty(duz))
            {
                throw new ArgumentNullException("Missing DUZ");
            }
            try
            {
                if (type == PermissionType.MenuOption)
                {
                    return getMenuOptions(duz);
                }
                if (type == PermissionType.DelegatedOption)
                {
                    return getDelegatedOptions(duz);
                }
                if (type == PermissionType.SecurityKey)
                {
                    return getSecurityKeys(duz);
                }
                throw new ArgumentException("Invalid type");
            }
            catch (ConnectionException ce)
            {
                throw new ArgumentException(ce.Message);
            }
        }

        //=========================================================================================
        // Permissions: options
        //=========================================================================================

        internal string addOption(string file, string context, string duz)
        {
            //Did we get a numeric DUZ?
            if (!StringUtils.isNumeric(duz))
            {
                throw new ArgumentException("Non-numeric user ID");
            }

            //Now make sure site has the requested option.  Otherwise a record will be written with a bad pointer to
            //file 19.
            string optionIen = getOptionIen(context);
            if (optionIen == "")
            {
                throw new ArgumentException("No such context");
            }

            DdrFiler query = buildAddOptionQuery(file, optionIen, duz);
            string response = query.execute();
            return toAddOptionResult(response);
        }

        internal DdrFiler buildAddOptionQuery(string file, string optionIen, string duz)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "ADD";
            query.Args = new string[]
            {
                file + "^.01^+1," + duz + ",^" + optionIen
            };
            return query;
        }

        internal string toAddOptionResult(string response)
        {
            if (response == "")
            {
                return "";
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            if (lines[0] == "[Data]" && lines.Length == 2)
            {
                return parseOptionNumber(response);
            }
            throw new DataException("addOption error: " + lines[lines.Length - 2]);
        }

        internal string parseOptionNumber(String rtn)
        {
            string[] lines = StringUtils.split(rtn, StringUtils.CRLF);
            if (lines[0] != "[Data]")
            {
                throw new UnexpectedDataException("Invalid return format (" + rtn + ")");
            }
            if (lines[1].StartsWith("[BEGIN_diERRORS]"))
            {
                throw new DataException(rtn.Substring(8));
            }
            if (lines.Length == 1)
            {
                throw new DataException("No option number data");
            }
            int p = lines[1].IndexOf(",^");
            string optNum = lines[1].Substring(p + 2);
            if (!StringUtils.isNumeric(optNum))
            {
                throw new UnexpectedDataException("Non-numeric option number");
            }
            return optNum;
        }

        internal string removeOption(string file, string optNum, string duz)
        {
            DdrFiler query = buildRemoveOptionQuery(file, optNum, duz);
            string response = query.execute();
            return toRemoveOptionResult(response);
        }

        internal DdrFiler buildRemoveOptionQuery(string file, string optNum, string duz)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "EDIT";
            query.Args = new string[]
            {
                file + "^.01^" + optNum + "," + duz + ",^@"
            };
            return query;
        }

        internal string toRemoveOptionResult(string response)
        {
            if (response == "")
            {
                return "";
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            lines = StringUtils.trimArray(lines);
            if (lines[0] == "[Data]" && lines.Length == 1)
            {
                return "OK";
            }
            if (lines[1] != VistaConstants.BEGIN_ERRS && lines[lines.Length - 1] != VistaConstants.END_ERRS)
            {
                return "Unexpected return message: " + response;
            }
            return "removeOption error: " + lines[lines.Length - 2];
        }

        internal Dictionary<string, AbstractPermission> getOptions(string file, string uid)
        {
            if (StringUtils.isEmpty(file) || StringUtils.isEmpty(uid))
            {
                throw new ArgumentNullException("Missing arguments");
            }
            DdrLister query = buildGetOptionsQuery(file, uid);
            string[] response = query.execute();
            return toOptions(response);
        }

        internal DdrLister buildGetOptionsQuery(string file, string uid)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = file;
            query.Iens = "," + uid + ",";

            // No worry about E flag since Screen arg rules out zombies
            query.Fields = ".01;.01E";

            query.Flags = "IP";
            query.Screen = "I $D(^DIC(19,$P(^(0),U,1),0))'=0";
            return query;
        }

        internal Dictionary<string, AbstractPermission> toOptions(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            Dictionary<string, AbstractPermission> result = new Dictionary<string, AbstractPermission>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                if (!result.ContainsKey(flds[0]))
                {
                    MenuOption opt = toOption(response[i]);
                    //addOptionFields(opt);
                    result.Add(flds[0], opt);
                }
            }
            return result;
        }

        internal MenuOption toOption(string response)
        {
            if (StringUtils.isEmpty(response))
            {
                return null;
            }
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            return new MenuOption(flds[1], flds[2], flds[0]);
        }

        internal void addOptionFields(VistaOption opt)
        {
            string arg = "$P($G(^DIC(19," + opt.PermissionId + ",0)),U,1)_U_" +
                            "$P(^(0),U,2)_U_" +
                            "$P(^(0),U,4)_U_" +
                            "$P(^(0),U,6)_U_" +
                            "$P($G(^DIC(19," + opt.PermissionId + ",3)),U,1)";
            string response = VistaUtils.getVariableValue(cxn, arg);
            string[] flds = StringUtils.split(response, StringUtils.CARET);
            opt.Name = flds[0];
            opt.DisplayName = flds[1];
            opt.OptionType = flds[2];
            if (flds[3] != "")
            {
                opt.Key = new SecurityKey(flds[3], "");
            }
            if (flds[4] != "")
            {
                opt.ReverseKey = new SecurityKey(flds[4], "");
            }
            return;
        }

        internal string getOptionIen(string optionName)
        {
            if (StringUtils.isEmpty(optionName))
            {
                throw new ArgumentNullException("Missing option name");
            }
            VistaQuery vq = new VistaQuery("XWB GET VARIABLE VALUE");
            string arg = "$O(^DIC(19,\"B\",\"" + optionName + "\",0))";
            vq.addParameter(vq.REFERENCE, arg);
            string response = (string)cxn.query(vq);
            return VistaUtils.errMsgOrIen(response);
        }

        internal bool hasOption(string duz, string optionName, string fieldNum)
        {
            if (StringUtils.isEmpty(duz) || StringUtils.isEmpty(optionName))
            {
                throw new ArgumentException("Invalid args");
            }
            string optIen = getOptionIen(optionName);
            string arg = "$D(^VA(200," + duz + "," + fieldNum + ",\"B\"," + optIen + "))";
            string result = VistaUtils.getVariableValue(cxn, arg);
            return (result != "0");
        }

        //=========================================================================================
        // Permissions: menu options
        //=========================================================================================

        internal string addMenuOption(string duz, AbstractPermission p)
        {
            return addOption(VistaConstants.MENU_OPTIONS_FILE, p.Name, duz);
        }

        internal void removeMenuOption(string duz, AbstractPermission p)
        {
            removeOption(VistaConstants.MENU_OPTIONS_FILE, p.RecordId, duz);
        }

        internal Dictionary<string, AbstractPermission> getMenuOptions(string duz)
        {
            Dictionary<string, AbstractPermission> result = new Dictionary<string, AbstractPermission>();
            AbstractPermission primaryOpt = getPrimaryMenuOption(duz);
            if (primaryOpt != null)
            {
                result.Add(primaryOpt.Name, primaryOpt);
            }
            Dictionary<string, AbstractPermission> secondaryOpts = getOptions(VistaConstants.MENU_OPTIONS_FILE, duz);
            if (secondaryOpts != null)
            {
                foreach (KeyValuePair<string, AbstractPermission> kvp in secondaryOpts)
                {
                    result.Add(kvp.Key, kvp.Value);
                }
            }
            return result;
        }

        internal AbstractPermission getPrimaryMenuOption(string duz)
        {
            string arg = "$P($G(^VA(200," + duz + ",201)),U,1)";
            string optionIen = VistaUtils.getVariableValue(cxn, arg);
            if (optionIen == "")
            {
                return null;
            }
            arg = "$P($G(^DIC(19," + optionIen + ",0)),U,1)";
            string optionName = VistaUtils.getVariableValue(cxn, arg);
            MenuOption result = new MenuOption();
            result.PermissionId = optionIen;
            result.Name = optionName;
            result.IsPrimary = true;
            return result;
        }

        internal bool hasMenuOption(string duz, AbstractPermission p)
        {
            return hasOption(duz, p.Name, VistaConstants.MENU_OPTIONS);
        }

        //=========================================================================================
        // Permissions: delegated options
        //=========================================================================================

        internal string addDelegatedOption(string duz, AbstractPermission p)
        {
            return addOption(VistaConstants.DELEGATED_OPTIONS_FILE, p.Name, duz);
        }

        public void removeDelegatedOption(string duz, AbstractPermission p)
        {
            removeOption(VistaConstants.DELEGATED_OPTIONS_FILE, p.RecordId, duz);
        }

        internal Dictionary<string, AbstractPermission> getDelegatedOptions(string duz)
        {
            return getOptions(VistaConstants.DELEGATED_OPTIONS_FILE, duz);
        }

        internal bool hasDelegatedOption(string duz, AbstractPermission p)
        {
            return hasOption(duz, p.Name, VistaConstants.DELEGATED_OPTIONS_FILE);
        }

        //=========================================================================================
        // Permissions: security keys
        //=========================================================================================

        public AbstractPermission addSecurityKey(string duz, AbstractPermission p)
        {
            // No empty args
            if (p == null || String.IsNullOrEmpty(p.Name) || String.IsNullOrEmpty(duz))
            {
                throw new ArgumentNullException("Missing arguments");
            }

            // No bogus security keys
            p.PermissionId = getSecurityKeyIen(p.Name);
            if (!StringUtils.isNumeric(p.PermissionId))
            {
                throw new ArgumentException("No such security key");
            }

            // No bogus users
            if (!isUser(duz))
            {
                throw new ArgumentException("No such user");
            }

            // Make sure user does not already have this key
            if (hasSecurityKey(duz, p))
            {
                throw new ArgumentException("User already has key");
            }

            p.RecordId = addSecurityKeyByName(p.Name, duz);
            return p;
        }

        public void removeSecurityKey(string duz, AbstractPermission p)
        {
            // No empty args
            if (p == null || String.IsNullOrEmpty(p.Name) || String.IsNullOrEmpty(duz))
            {
                throw new ArgumentNullException("Missing arguments");
            }

            // No bogus security keys
            p.PermissionId = getSecurityKeyIen(p.Name);
            if (!StringUtils.isNumeric(p.PermissionId))
            {
                throw new ArgumentException("No such security key");
            }

            // No bogus users
            if (!isUser(duz))
            {
                throw new ArgumentException("No such user");
            }

            // Make sure user has this key
            if (!hasSecurityKey(duz, p))
            {
                throw new ArgumentException("User does not have key");
            }

            // Do it
            DdrFiler query = buildRemoveSecurityKeyByIenQuery(p.PermissionId, duz);
            string response = query.execute();
            if (response == "")
            {
                throw new UnexpectedDataException("Empty response");
            }
            if (response == "[Data]\r\n")
            {
                return;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            if (lines[0] != "[Data]")
            {
                throw new UnexpectedDataException("Unexpected response: " + response);
            }
            if (lines.Length > 1)
            {
                if (lines[1] != "[BEGIN_diERRORS]" || lines.Length < 5)
                {
                    throw new UnexpectedDataException("Unexpected response: " + response);
                }
                throw new ArgumentException(lines[4]);
            }
        }

        public string getSecurityKeyForContext(string context)
        {
            if (StringUtils.isEmpty(context))
            {
                throw new ArgumentNullException("Missing context");
            }
            string contextIen = getOptionIen(context);
            VistaQuery vq = new VistaQuery("XWB GET VARIABLE VALUE");
            string arg = "$G(^DIC(19," + contextIen + ",0))";
            vq.addParameter(vq.REFERENCE, arg);
            string response = (string)cxn.query(vq);
            return extractSecurityKey(response);
        }

        public string addSecurityKeyForContext(string duz, string context)
        {
            return null;
            //if (StringUtils.isEmpty(duz) || StringUtils.isEmpty(context))
            //{
            //    throw new ArgumentNullException("Missing arguments");
            //}
            //string securityKey = getSecurityKeyForContext(context);
            //if (securityKey == "")
            //{
            //    throw new ArgumentException("No security key for context " + context);
            //}
            //return addSecurityKey(duz, securityKey);
        }

        internal Dictionary<string, AbstractPermission> getSecurityKeys(string duz)
        {
            DdrLister query = buildGetSecurityKeysQuery(duz);
            string[] response = query.execute();
            return toUserSecurityKeys(response);
        }

        internal string getSecurityKeyIen(string securityKeyName)
        {
            string arg = "$O(^DIC(19.1,\"B\",\"" + securityKeyName + "\",0))";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        internal string addSecurityKeyByName(string securityKeyName, string duz)
        {
            if (StringUtils.isEmpty(securityKeyName) || StringUtils.isEmpty(duz))
            {
                throw new ArgumentNullException("Missing arguments");
            }
            DdrLister query = buildAddSecurityKeyByNameQuery(securityKeyName, duz);
            string[] response = query.execute();
            if (response == null || response.Length == 0)
            {
                throw new ArgumentException("Unable to add security key " + securityKeyName + " for user " + duz);
            }
            string[] flds = StringUtils.split(response[0], StringUtils.CARET);
            if (flds[1] != securityKeyName)
            {
                throw new DataException("Error adding security key: " + response);
            }
            return flds[0];
        }

        internal DdrLister buildAddSecurityKeyByNameQuery(string securityKeyName, string duz)
        {
            // Can't use DDR FILER because the underlying M function requires 2 arrays and the
            // broker can only do 1.
            // Idea is to make a query that is guaranteed to find a record, then use the identifier
            // argument to add the new record.
            DdrLister query = new DdrLister(cxn);
            query.File = "19.1";
            query.Fields = ".01";
            query.Flags = "IP";
            query.From = VistaUtils.adjustForNameSearch(securityKeyName);
            query.Part = securityKeyName;
            query.Xref = "B";
            query.Id = "X \"S XQKUS=" + duz + " S XQKEY=$P(^(0),U,1) S XQKF=0 D ADD^XQKEY(XQKUS,XQKEY,XQKF) D EN^DDIOL(XQKF)\"";
            return query;
        }

        internal string extractSecurityKey(string response)
        {
            return StringUtils.piece(response, "^", 6);
        }

        internal DdrLister buildGetSecurityKeysQuery(string duz)
        {
            DdrLister query = new DdrLister(cxn);
            query.File = "200.051";
            query.Iens = "," + duz + ",";
            query.Fields = ".01;1;2;3";
            query.Flags = "IP";
            return query;
        }

        internal Dictionary<string, AbstractPermission> toUserSecurityKeys(string[] response)
        {
            if (response == null || response.Length == 0)
            {
                return null;
            }
            StringDictionary securityKeyFile = cxn.SystemFileHandler.getLookupTable(VistaConstants.SECURITY_KEY);
            Dictionary<string, AbstractPermission> result = new Dictionary<string, AbstractPermission>(response.Length);
            for (int i = 0; i < response.Length; i++)
            {
                string[] flds = StringUtils.split(response[i], StringUtils.CARET);
                if (securityKeyFile.ContainsKey(flds[1]))
                {
                    AbstractPermission key = new SecurityKey(flds[1], securityKeyFile[flds[1]], flds[0]);
                    result.Add(key.Name, key);
                }
            }
            return result;
        }

        internal DdrFiler buildRemoveSecurityKeyByIenQuery(string securityKeyIen, string duz)
        {
            DdrFiler query = new DdrFiler(cxn);
            query.Operation = "EDIT";
            query.Args = new string[]
            {
                "200.051^.01^" + securityKeyIen + "," + duz + ",^@"
            };
            return query;
        }

        internal string getSecurityKeyDescriptiveName(string ien)
        {
            string arg = "$P($G(^DIC(19.1," + ien + ",0)),U,2)";
            return VistaUtils.getVariableValue(cxn, arg);
        }

        internal bool hasSecurityKey(AbstractPermission p)
        {
            return hasSecurityKey(cxn.Uid, p);
        }

        internal bool hasSecurityKey(string duz, AbstractPermission p)
        {
            MdoQuery request = buildHasSecurityKeyRequest(duz, p.Name);
            string response = (string)cxn.query(request);
            return (response == "1");
        }

        internal MdoQuery buildHasSecurityKeyRequest(string duz, string key)
        {
            VistaQuery vq = new VistaQuery("ORWU NPHASKEY");
            vq.addParameter(vq.LITERAL, duz);
            vq.addParameter(vq.LITERAL, key);
            return vq;
        }

        /// <summary>
        /// Get all the users with a certain option
        /// Currently this is from the Standing Sentry project and only gets
        /// users with option AMOJ VL APPTFL.  Should be generalized.
        /// </summary>
        /// <param name="optionName"></param>
        /// <returns></returns>
        public OrderedDictionary getUsersWithOption(string optionName)
        {
            MdoQuery request = buildGetUsersWithOptionRequest(optionName);
            string response = (string)cxn.query(request, new MenuOption("AMOJ VL APPTFL"));
            return toUsersWithOption(response);
        }

        internal MdoQuery buildGetUsersWithOptionRequest(string optionName)
        {
            VistaQuery vq = new VistaQuery("AMOJ VL APPTFL GET PROVIDERS");
            return vq;
        }

        internal OrderedDictionary toUsersWithOption(string response)
        {
            if (String.IsNullOrEmpty(response))
            {
                return null;
            }
            string[] lines = StringUtils.split(response, StringUtils.CRLF);
            OrderedDictionary result = new OrderedDictionary();
            for (int i = 0; i < lines.Length; i++)
            {
                if (String.IsNullOrEmpty(lines[i]))
                {
                    continue;
                }
                string[] flds = StringUtils.split(lines[i], StringUtils.CARET);
                result.Add(flds[1], flds[0]);
            }
            return result;
        }

        //public string getAppointmentsForProvider(string duz, int days)
        //{
        //    string request = buildGetAppointmentsForProviderRequest(duz, days);
        //    string response = (string)cxn.query(request);
        //    return response;
        //}

        //internal string buildGetAppointmentsForProviderRequest(string duz, int days)
        //{
        //    VistaQuery vq = new VistaQuery("AMOJ VL APPTFL PROVIDER APPTS");
        //    vq.addParameter(vq.LITERAL, duz);
        //    vq.addParameter(vq.LITERAL, days.ToString());
        //    return vq;
        //}


        public void updateUser(User user, string property, object value)
        {
            throw new NotImplementedException();
        }

        public void updateUser(User user, Dictionary<string, object> properties)
        {
            throw new NotImplementedException();
        }


        public IList<User> userLookupList(KeyValuePair<string, string> param)
        {
            throw new NotImplementedException();
        }
    }
}
