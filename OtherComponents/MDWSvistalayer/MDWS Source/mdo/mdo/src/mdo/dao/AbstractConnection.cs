using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.src.mdo;
using gov.va.medora.mdo.domain.pool;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao
{
    public abstract class AbstractConnection : AbstractTimedResource
    {
        public DateTime LastUsed { get; set; }
        public Dictionary<string, object> Session { get; set; }
        public Guid ConnectionId { get; set; }
        public bool IsAvailable { get; set; }
        public DateTime LastQueryTimestamp { get; set; }
        DataSource _dataSource;
        string _uid;
        string _pid;
        string _welcomeMsg;
        bool _isTestSource;
        AbstractAccount _account;
        bool _isRemote;
        int _connectTimeout = 0;
        int _readTimeout = 0;

        IConnectStrategy _connectStrategy;

        protected bool _connected = false;
        protected bool _connecting = false;

        public AbstractConnection(DataSource dataSource)
        {
            this._dataSource = dataSource;
        }

        public DataSource DataSource
        {
            get { return _dataSource; }
            set { _dataSource = value; }
        }

        public AbstractAccount Account
        {
            get { return _account; }
            set { _account = value; }
        }

        public string Uid
        {
            get { return _uid; }
            set { _uid = value; }
        }

        public string Pid
        {
            get { return _pid; }
            set { _pid = value; }
        }

        public bool IsTestSource
        {
            get { return _isTestSource; }
            set { _isTestSource = value; }
        }

        public bool IsRemote
        {
            get { return _isRemote; }
            set { _isRemote = value; }
        }

        public abstract ISystemFileHandler SystemFileHandler
        {
            get;
        }

        public IConnectStrategy ConnectStrategy
        {
            get { return _connectStrategy; }
            set { _connectStrategy = value; }
        }

        public string WelcomeMessage
        {
            get { return _welcomeMsg; }
            set { _welcomeMsg = value; }
        }

        public virtual bool IsConnected
        {
            get { return _connected; }
            set { _connected = value; }
        }

        public bool IsConnecting
        {
            get { return _connecting; }
            set { _connecting = value; }
        }

        public int ConnectTimeout
        {
            get { return _connectTimeout; }
            set { _connectTimeout = value; }
        }


        public int ReadTimeout
        {
            get { return _readTimeout; }
            set { _readTimeout = value; }
        }

        public Object getDao(String daoName)
        {
            AbstractDaoFactory df = AbstractDaoFactory.getDaoFactory(AbstractDaoFactory.getConstant(_dataSource.Protocol));
            return df.getDaoByName(daoName, this);
        }

        public abstract void connect();
        //public abstract object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission);
        public abstract object authorizedConnect(AbstractCredentials credentials, AbstractPermission permission, DataSource validationDataSource);
        public abstract string getWelcomeMessage();
        public abstract bool hasPatch(string patchId);
        public abstract object query(MdoQuery request, AbstractPermission permission = null);
        public abstract object query(string request, AbstractPermission permission = null);
        public abstract object query(SqlQuery request, Delegate functionToInvoke, AbstractPermission permission = null);
        public abstract string getServerTimeout();
        public abstract void disconnect();
        public abstract Dictionary<string, object> getState();
        public abstract void setState(Dictionary<string, object> session);

        public override int GetHashCode()
        {
            return ToString().GetHashCode();
        }

        public override string ToString()
        {
            return ConnectionId.ToString();
        }

        public override void CleanUp()
        {
            try
            {
                this.disconnect();
            }
            catch (Exception exc)
            {
                LogUtils.getInstance().Log("Error on clean up: " + exc.ToString());
                throw;
            }
            finally
            {
                this.IsConnected = false;
            }
        }
    }
}
