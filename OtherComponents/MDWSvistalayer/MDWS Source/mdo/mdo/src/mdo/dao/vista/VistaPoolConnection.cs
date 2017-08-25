using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using gov.va.medora.mdo.domain.pool;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaPoolConnection : VistaConnection
    {
        internal AbstractResourceState _state;

        internal Dictionary<string, object> _rawConnectionSymbolTable;

        public DateTime _lastUsed = DateTime.Now;
        public DateTime LastUsed { get { return _lastUsed; } set { _lastUsed = value; } }

        Int32 _queryCount = 0;
        public Int32 QueryCount { get { return _queryCount; } } 

        public VistaPoolConnection(DataSource ds) : base(ds) { }

        public override object query(MdoQuery vq, AbstractPermission context = null)
        {
            return this.query(true, vq, context);
        }

        public override object query(string request, AbstractPermission context = null)
        {
            _queryCount++; // all requests pass through here so only increment in this one place
            return this.query(true, request, context);
        }

        // the disconnect message was resetting the timeout timer!!! so, to get around this, this class
        // implements its own disconnect that signals these methods to not reset the timer
        object query(bool resetTimer, MdoQuery vq, AbstractPermission context = null)
        {
            if (resetTimer)
            {
                base.resetTimer();
            }
            return base.query(vq, context);
        }

        object query(bool resetTimer, string request, AbstractPermission context = null)
        {
            if (resetTimer)
            {
                base.resetTimer();
            }
            return base.query(request, context);
        }

        public override void connect()
        {
            if (!IsConnected) // don't connect if already connected - causes socket to be disposed
            {
                base.connect();
            }
        }

        public override void disconnect()
        {
            base.disconnect();
            return;

            //if (!IsConnected)
            //{
            //    return;
            //}

            //try
            //{
            //    string msg = "[XWB]10304\x0005#BYE#\x0004";
            //    msg = (string)query(false, msg);
            //    socket.Close();
            //}
            //catch (Exception) { }
            //finally
            //{
            //    IsConnected = false;
            //}
        }

        //void disconnect(object state)
        //{
        //    _state = (AbstractResourceState)state;
        //    try
        //    {
        //        ((AbstractConnection)_state.Resource).disconnect();
        //    }
        //    catch (Exception) { }
        //}

        public override bool IsConnected
        {
            get
            {
                if (socket == null)
                {
                    return false;
                }
                if (!socket.Connected)// ths pooled connection has special needs for checking the state of a connection - examine the underlying socket directly!
                {
                    base.IsConnected = false;
                }
                return base.IsConnected; 
            }
        }

        internal void resetRaw()
        {
            base.setState(_rawConnectionSymbolTable);
            _lastUsed = DateTime.Now;
        }


        internal void shutDown()
        {
            base.disconnect();
        }
    }
}
