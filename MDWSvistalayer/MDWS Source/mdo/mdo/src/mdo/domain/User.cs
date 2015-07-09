using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.mdo
{
    public class User : Person
    {
        string userName;
        string nickName;
        string pwd;
        string permissionString;
        string uid;
        SiteId logonSiteId;
        Team team;
        string office;
        Service service;
        string voicePager;
        string digitalPager;
        string title;
        string phone;
        string sigText;
        string esig;
        string userClass;
        string orderRole;
        string greeting;
        string emailAddress;
        string currentContext;
        bool defunct;
        bool canWriteMedOrders;
        string inactiveDate;
        bool isInactive;
        string providerClass;
        string providerType;
        bool requiresCosigner;
        User usualCosigner;
        AbstractPermission primaryPermission;
        string _domain;

        public User() { }

        public string Domain
        {
            get { return _domain; }
            set { _domain = value; }
        }

        public string Uid
        {
            get { return uid; }
            set { uid = value; }
        }

        public string Pwd
        {
            get { return pwd; }
            set { pwd = value; }
        }

        public string PermissionString
        {
            get { return permissionString; }
            set { permissionString = value; }
        }

        public string UserName
        {
            get { return userName; }
            set { userName = value; }
        }

        public SiteId LogonSiteId
        {
            get { return logonSiteId; }
            set { logonSiteId = value; }
        }

        public string SigText
        {
            get { return sigText; }
            set { sigText = value; }
        }

        public string Esig
        {
            get { return esig; }
            set { esig = value; }
        }

        public string UserClass
        {
            get { return userClass; }
            set { userClass = value; }
        }

        public Team Team
        {
            get { return team; }
            set { team = value; }
        }

        public string Office
        {
            get { return office; }
            set { office = value; }
        }

        public Service Service
        {
            get { return service; }
            set { service = value; }
        }

        public string VoicePager
        {
            get { return voicePager; }
            set { voicePager = value; }
        }

        public string DigitalPager
        {
            get { return digitalPager; }
            set { digitalPager = value; }
        }

        public string Title
        {
            get { return title; }
            set { title = value; }
        }

        public string Phone
        {
            get { return phone; }
            set { phone = value; }
        }

        public string OrderRole
        {
            get { return orderRole; }
            set { orderRole = value; }
        }

        public string Greeting
        {
            get { return greeting; }
            set { greeting = value; }
        }

        public string EmailAddress
        {
            get { return emailAddress; }
            set { emailAddress = value; }
        }

        public string CurrentContext
        {
            get { return currentContext; }
            set { currentContext = value; }
        }

        public bool Defunct
        {
            get { return defunct; }
            set { defunct = value; }
        }

        public string NickName
        {
            get { return nickName; }
            set { nickName = value; }
        }

        public bool CanWriteMedOrders
        {
            get { return canWriteMedOrders; }
            set { canWriteMedOrders = value; }
        }

        public string InactiveDate
        {
            get { return inactiveDate; }
            set 
            { 
                inactiveDate = value;
                if (inactiveDate != "")
                {
                    isInactive = true;
                }
            }
        }

        public bool IsInactive
        {
            get { return isInactive; }
            set { isInactive = value; }
        }

        public string ProviderClass
        {
            get { return providerClass; }
            set { providerClass = value; }
        }

        public string ProviderType
        {
            get { return providerType; }
            set { providerType = value; }
        }

        public bool RequiresCosigner
        {
            get { return requiresCosigner; }
            set { requiresCosigner = value; }
        }

        public User UsualCosigner
        {
            get { return usualCosigner; }
            set { usualCosigner = value; }
        }

        public AbstractPermission PrimaryPermission
        {
            get { return primaryPermission; }
            set { primaryPermission = value; }
        }

        internal static IUserDao getDao(AbstractConnection cxn)
        {
            if (!cxn.IsConnected)
            {
                throw new MdoException(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: unconnected");
            }
            AbstractDaoFactory f = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(cxn.DataSource.Protocol));
            return f.getUserDao(cxn);
        }

        public static OrderedDictionary getUsersWithOption(AbstractConnection cxn, string optionName)
        {
            return getDao(cxn).getUsersWithOption(optionName);
        }

        public static bool hasPermission(AbstractConnection cxn, string userId, AbstractPermission permission)
        {
            return getDao(cxn).hasPermission(userId, permission);
        }

        public static Dictionary<string, AbstractPermission> getPermissions(AbstractConnection cxn, string uid, PermissionType type)
        {
            return getDao(cxn).getPermissions(type, uid);
        }

    }
}
